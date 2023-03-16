<?php

declare(strict_types=1);

namespace App\Helper;

class TokenHelper
{
    public const ERC20_TOKENS = [
        'APE' => '0x4d224452801aced8b2f0aebe155379bb5d594381',
        'BR' => '0xe21363bf33620a291a6c354dc3bb99e40cb3086b',
        'CMT' => '0xe910c2a090516fb7a7be07f96a464785f2d5dc18',
        'DEV' => '0xa3d59c6d24f428dcfedc09724869e7af4d281fdd',
        'ETH' => '',
        'GODS' => '0xccc8cb5229b0ac8069c51fd58367fd1e622afd97',
        'GOG' => '0x9ab7bb7fdc60f4357ecfef43986818a2a3569c62',
        'IMX' => '0xf57e7e7c23978c3caec3c3548e3d615c346e79ff',
        'LOST' => '0x71854072ce51cc8859c8c178e33581034fa75753',
        'OMI' => '0xed35af169af46a02ee13b9d79eb57d6d68c1749e',
        'SILV2' => '0x7e77dcb127f99ece88230a64db8d595f31f1b068',
        'USDC' => '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48',
        'VCO' => '0x2caa4021e580b07d92adf8a40ec53b33a215d620',
        'VCORE' => '0x733b5056a0697e7a4357305fe452999a0c409feb',
    ];

    public static function getTokenName(string $tokenContract): string
    {
        return array_search($tokenContract, self::ERC20_TOKENS, true) ?: $tokenContract;
    }
}
