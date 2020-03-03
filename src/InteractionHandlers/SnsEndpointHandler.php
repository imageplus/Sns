<?php


namespace Imageplus\Sns\src\InteractionHandlers;


use Imageplus\Sns\src\Contracts\SnsEndpointContract;
use Imageplus\Sns\src\Facades\Sns;
use Imageplus\Sns\src\Traits\instancesSnsModels;
use Imageplus\Sns\src\Traits\validatesObjects;
use Aws\Result;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

/**
 * Creates an endpoint in SNS
 * Class SnsEndpointHandler
 * @package Imageplus\Sns\InteractionHandlers
 */
class SnsEndpointHandler
{
    use instancesSnsModels, validatesObjects;

    /**
     * Gets an endpoint bu creating or returning it
     * @param $platform
     * @param $device_token
     * @return SnsEndpointContract|bool
     */
    public function getEndpoint($platform, $device_token){
        //try to get the endpoint for the device
        $deviceEndpoint = $this->model::forDevice($device_token, $platform);

        //if the endpoint exists return it otherwise create it
        return
            $deviceEndpoint->first() ?? $this->createEndpoint($platform, $device_token);
    }

    /**
     * Creates a new endpoint within sns
     * @param $device_platform
     * @param $device_token
     * @return SnsEndpointContract|bool
     */
    protected function createEndpoint($device_platform, $device_token){

        $credentials = $this->getCredentialsForPlatform($device_platform);

        if(!$credentials){
            return false;
        }

        //handles the endpoint in sns
        $endpoint = $this->createEndpointForPlatform(
            $credentials,
            $device_token
        );

        //Creates the record in the database
        return $this->model::create([
            //$endpoint in an Sns response so use it
            'endpoint_arn' => $endpoint->get('EndpointArn'),
            'device_token' => $device_token,
            'platform' => $device_platform,
            'user_agent' => Request::header('user-agent')
        ]);
    }

    /**
     * Creates the endpoint in sns for the given device
     * @param $credentials
     * @param $device_token
     * @return Result
     */
    protected function createEndpointForPlatform($credentials, $device_token){
        //create the endpoint in sns
        return Sns::getClient()->createPlatformEndpoint([
            'PlatformApplicationArn' => $credentials,
            'Token' => $device_token
        ]);

        //TODO: ADD EXCEPTION FOR FAIlURE
    }

    /**
     * Gets the arn for the given platform
     * @param $device_platform
     * @return String|void
     */
    protected function getCredentialsForPlatform($device_platform){
        //if the platform exists return its arn from the config
        $platform_arn = config(
            'sns.platform_arns.' .
            Str::lower($device_platform)
        );

        return $this->validate(
            [ 'platform_arn' => 'required|array|size:6' ],
            [ 'platform_arn' => explode(':', $platform_arn) ]
        )
            ? $platform_arn
            : false;
        //TODO: THROW ERROR IF PLATFORM IS INVALID
    }

    /**
     * Deletes an endpoint from sns and the database
     * @param SnsEndpointContract $endpoint
     * @return bool
     */
    public function removeEndpoint(SnsEndpointContract $endpoint){
        //removes the endpoint from sns
        Sns::getClient()->deleteEndpoint([
            'EndpointArn' => $endpoint->endpoint_arn
        ]);

        //TODO: ADD EXCEPTION FOR FAILURE

        //remove the endpoint from the database
        return $endpoint->delete();
    }

    /**
     * Used when force resetting an endpoint to get the endpoint
     * @param $device_token
     * @param $platform
     * @return mixed
     */
    public function findEndpoint($device_token, $platform){
        //return the first endpoint for the device
        return $this->model::forDevice($device_token, $platform)->first();
    }
}
