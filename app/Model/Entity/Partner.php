<?php

namespace Model\Entity;

class Partner
{
    private string $continent;
    private string $country;
    private string $city;
    private string $institution;
    private string $type;

    // FIX: $type was missing from the constructor parameters, causing "Undefined variable: $type"
    public function __construct(string $continent, string $country, string $city, string $institution, string $type)
    {
        $this->continent   = $continent;
        $this->country     = $country;
        $this->city        = $city;
        $this->institution = $institution;
        $this->type        = $type;
    }

    public function getContinent(): string
    {
        return $this->continent;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getInstitution(): string
    {
        return $this->institution;
    }

    public function getType(): string
    {
        return $this->type;
    }
}