import requests
from neo4j import GraphDatabase

# Configure the API and Neo4j database
API_ENDPOINT = "https://api.x.immutable.com"
NEO4J_URI = "bolt://localhost:7687"
NEO4J_USER = ""
NEO4J_PASSWORD = ""

driver = GraphDatabase.driver(NEO4J_URI, auth=(NEO4J_USER, NEO4J_PASSWORD))

toSkip = ["0x45604a7e44759c6269aff60505b32ccd5445c103",
          "0x6ebc6ce4c59cdbdf8065d6cd4f06ee1d221d5eb0",
          "0x0000000000000000000000000000000000000000"]


def get_transfers(cursor=None):
    endpoint = f"{API_ENDPOINT}/v1/transfers"
    headers = {
        "Content-Type": "application/json"
    }
    params = {
        'cursor': cursor,
        "min_timestamp": "2023-05-15T23:59:00Z",
        "max_timestamp": "2023-05-18T00:00:00Z",
        'token_address': '0xb446b96b931f0cc59b89d584d32cf1466406895c'
    }

    response = requests.get(endpoint, headers=headers, params=params)
    if response.status_code == 200:
        result = response.json()["result"]
        next_cursor = response.json()["cursor"]
        return result, next_cursor
    else:
        raise Exception("Failed to retrieve transfers")


def save_transfers(transfer):
    with driver.session() as session:
        for transfer in transfer:
            if transfer["user"] in toSkip or transfer["receiver"] in toSkip:
                continue
            if transfer["token"]['type'] == "ERC20":
                continue

            tx_hash = transfer["transaction_id"]
            from_address = transfer["user"]
            to_address = transfer["receiver"]
            nft_token_id = transfer["token"]['data']["token_id"]
            nft_token_address = transfer["token"]['data']["token_address"]
            nft_quantity = transfer["token"]['data']["quantity"]

            print(transfer)
            # Create nodes for addresses and transfer
            session.run(
                """
                MERGE (from:Address {address: $from_address})
                MERGE (to:Address {address: $to_address})
                MERGE (transfer:Transfer {hash: $tx_hash})
                MERGE (nft:NFT {token_id: $nft_token_id, token_address: $nft_token_address})
                """,
                from_address=from_address,
                to_address=to_address,
                tx_hash=tx_hash,
                nft_token_id=nft_token_id,
                nft_token_address=nft_token_address
            )

            # Create relationships between source address and transfer
            session.run(
                """
                MATCH (from:Address {address: $from_address})
                MATCH (transfer:Transfer {hash: $tx_hash})
                MERGE (from)-[:SENT]->(transfer)
                """,
                from_address=from_address,
                tx_hash=tx_hash
            )

            # Create relationships between transfer and destination address
            session.run(
                """
                MATCH (transfer:Transfer {hash: $tx_hash})
                MATCH (to:Address {address: $to_address})
                MERGE (transfer)-[:RECEIVED]->(to)
                """,
                tx_hash=tx_hash,
                to_address=to_address
            )

            # Create relationships between transfer and NFT
            session.run(
                """
                MATCH (transfer:Transfer {hash: $tx_hash})
                MATCH (nft:NFT {token_id: $nft_token_id, token_address: $nft_token_address})
                MERGE (transfer)-[:EXCHANGED {quantity: $nft_quantity}]->(nft)
                """,
                tx_hash=tx_hash,
                nft_token_id=nft_token_id,
                nft_token_address=nft_token_address,
                nft_quantity=nft_quantity
            )


def retrieve_and_save_transfers():
    cursor = None
    while True:
        transfers, next_cursor = get_transfers(cursor)
        save_transfers(transfers)
        if next_cursor:
            cursor = next_cursor
        else:
            break


retrieve_and_save_transfers()

driver.close()
