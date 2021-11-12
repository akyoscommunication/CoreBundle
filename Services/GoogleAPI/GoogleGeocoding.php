<?php

namespace Akyos\CoreBundle\Services\GoogleAPI;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleGeocoding
{
	private $googleApiKey;
	
	public function __construct(ParameterBagInterface $params)
	{
		$this->googleApiKey = $params->get('google_apiKey');
	}

    /**
     * @param $address
     * @return array|Exception
     */
	public function geocodeAddress($address)
	{
		$address = urlencode($address);
		$url = 'https://maps.google.com/maps/api/geocode/json?address=' . $address . '&key=' . $this->googleApiKey;
		
		try {
			$JSONResponse = file_get_contents($url);
			$response = json_decode($JSONResponse, true, 512, JSON_THROW_ON_ERROR);
			
			if ($response['status'] === 'OK') {
				
				$latitude = $response['results'][0]['geometry']['location']['lat'];
				$longitude = $response['results'][0]['geometry']['location']['lng'];
				$formatted_address = $response['results'][0]['formatted_address'];
				
				if ($latitude && $longitude && $formatted_address) {
					
					return [
						'latitude' => $latitude,
						'longitude' => $longitude,
						'formatted_address' => $formatted_address,
					];
					
				}

                throw new RuntimeException('Google API didn\'t return entire datas, please verify Google API configuration');
            }
            throw new RuntimeException('Google API return errored status code: ' . $response['status']);
        } catch (Exception $e) {
			return $e;
		}
	}
}