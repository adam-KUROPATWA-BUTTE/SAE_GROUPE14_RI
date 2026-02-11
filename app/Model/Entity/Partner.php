<?php

namespace Model\Entity;

class Partner
{
    private string $continent;
    private string $country;
    private string $city;
    private string $institution;

    public function __construct(string $continent, string $country, string $city, string $institution)
    {
        $this->continent = $continent;
        $this->country = $country;
        $this->city = $city;
        $this->institution = $institution;
    }

    public function getContinent(): string { return $this->continent; }
    public function getCountry(): string { return $this->country; }
    public function getCity(): string { return $this->city; }
    public function getInstitution(): string { return $this->institution; }
}
