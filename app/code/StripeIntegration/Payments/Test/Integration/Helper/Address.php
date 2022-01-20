<?php

namespace StripeIntegration\Payments\Test\Integration\Helper;

class Address
{
    public function __construct(
        \Magento\Directory\Model\RegionFactory $regionFactory
    )
    {
        $this->regionFactory = $regionFactory;
    }

    public function getMagentoFormat($identifier)
    {
        switch ($identifier)
        {
            case 'NewYork':
                return [
                    'telephone' => "917-535-4022",
                    'postcode' => "10013",
                    'country_id' => 'US',
                    'region_id' => 43, // 43 = 8.375%
                    'city' => 'New York',
                    'street' => ['1255 Duncan Avenue'],
                    'lastname' => 'Jerry',
                    'firstname' => 'Flint',
                    'email' => 'flint@example.com',
                ];
            case 'California':
                return [
                    'telephone' => "626-945-7637",
                    'postcode' => "91752",
                    'country_id' => 'US',
                    'region_id' => 12, // 12 = 8.25%
                    'city' => 'Mira Loma',
                    'street' => ['2974 Providence Lane'],
                    'lastname' => 'Strother',
                    'firstname' => 'Joyce',
                    'email' => 'joyce@example.com',
                ];
            case 'Mexico':
                return [
                    'telephone' => "771.715-2115",
                    'postcode' => "42000",
                    'country_id' => 'MX',
                    'region_id' => 934, // Puebla
                    'city' => 'HIDALGO',
                    'street' => ['GUERRERO NO. 521', 'PACHUCA DE SOTO CENTRO'],
                    'lastname' => 'Hopi',
                    'firstname' => 'Huyana',
                    'email' => 'huyana@example.com',
                ];
            case 'Michigan':
                return [
                    'telephone' => "701-270-0720",
                    'postcode' => "58259",
                    'country_id' => 'US',
                    'region_id' => 33, // 33 = 8.25%
                    'city' => 'Michigan',
                    'street' => ['3510 Catherine Drive'],
                    'lastname' => 'Cook',
                    'firstname' => 'Crystal',
                    'email' => 'crystal@example.com',
                ];
            case 'Berlin':
                return [
                    'telephone' => "030 63 38673",
                    'postcode' => "13469",
                    'country_id' => 'DE',
                    'region_id' => 82,
                    'city' => 'Berlin Lübars',
                    'street' => ['Brandenburgische Straße 41'],
                    'lastname' => 'Osterhagen',
                    'firstname' => 'Mario',
                    'email' => 'osterhagen@example.com',
                ];
            case 'Belgium':
                return [
                    'telephone' => "0490 83 32254",
                    'postcode' => "8510",
                    'country_id' => 'BE',
                    'region_id' => null,
                    'city' => 'Bellegem',
                    'street' => ['Eikstraat 388'],
                    'lastname' => 'Eelman',
                    'firstname' => 'Arjuna',
                    'email' => 'eelman@example.com',
                ];
            case 'London':
                return [
                    'telephone' => "078 7218 3826",
                    'postcode' => "SW1Y 5JH",
                    'country_id' => 'GB',
                    'region_id' => null,
                    'city' => 'London',
                    'street' => ['44 Crown Street'],
                    'lastname' => 'Parker',
                    'firstname' => 'Harry',
                    'email' => 'parker@example.com'
                ];
            case 'Malaysia':
                return [
                    'telephone' => "607-2376867",
                    'postcode' => "81200",
                    'country_id' => 'MY',
                    'region_id' => null,
                    'city' => 'Johor Bahru',
                    'street' => ['101A Jalan Persisiran Perling Taman'],
                    'lastname' => 'Kembang',
                    'firstname' => 'Putri',
                    'email' => 'kembang@example.com'
                ];
            case 'Brazil':
                return [
                    'telephone' => "(11) 5456-7271",
                    'postcode' => "09051-020",
                    'country_id' => 'BR',
                    'region_id' => 508,
                    'city' => 'Santo André',
                    'street' => ['Praça Cândido Portinari 1129'],
                    'lastname' => 'Pinto',
                    'firstname' => 'Leila',
                    'email' => 'pinto@example.com'
                ];
            case 'Canada':
                return [
                    'telephone' => "250-384-2275",
                    'postcode' => "V8W 2H9",
                    'country_id' => 'CA',
                    'region_id' => 67, // British Columbia
                    'city' => 'Victoria',
                    'street' => ['2181 Blanshard'],
                    'lastname' => 'Hamon',
                    'firstname' => 'Fawn',
                    'email' => 'hamon@example.com'
                ];
            case 'Australia':
                return [
                    'telephone' => "(07) 4916 6836",
                    'postcode' => "4680",
                    'country_id' => 'AU',
                    'region_id' => 608, // Queensland
                    'city' => 'O\'CONNELL',
                    'street' => ['66 Ronald Crescent'],
                    'lastname' => 'Kidman',
                    'firstname' => 'Declan',
                    'email' => 'declan@example.com'
                ];
            default:
                throw new \Exception("No such address $identifier");
        }
    }

    public function getStripeFormat($identifier)
    {
        $address = $this->getMagentoFormat($identifier);

        if ($address['region_id'])
        {
            $region = $this->regionFactory->create()->load($address['region_id']);
            $state = $region->getName();
        }
        else
        {
            $state = null;
        }

        return [
            'address' => [
                'city' => $address['city'],
                'country' => $address['country_id'],
                'line1' => $address['street'][0],
                'postal_code' => $address['postcode'],
                'state' => $state
            ],
            'email' => $address['email'],
            'name' => $address['firstname'] . " " . $address['lastname'],
            'phone' => $address['telephone']
        ];
    }

    public function getStripeShippingFormat($identifier)
    {
        $address = $this->getStripeFormat($identifier);
        unset($address["email"]);
        return $address;
    }
}
