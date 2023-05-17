# IMX Wash Trading Data
Not for FUD


## Installation

```bash
git clone git@github.com:pgrimaud/immutable-x-wash-trading-data.git
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

## Usage

#### Import trades

```bash
php bin/console app:trades 2023-05-15
php bin/console app:update-trades 2023-05-15
```

#### Import transfers

```bash
php bin/console app:transfers 2023-05-15
```


#### Fetch collection data

```bash
php bin/console app:collections
```