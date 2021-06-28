<?php


namespace Imageplus\Sns\InteractionHandlers;


use Imageplus\Sns\Contracts\SnsEndpointContract;
use Imageplus\Sns\Exceptions\DevicePlatformDoesNotExistException;
use Imageplus\Sns\Exceptions\PlatformArnNotValidException;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\Traits\instancesSnsModels;
use Imageplus\Sns\Traits\validatesObjects;
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
     * Gets an endpoint by creating or returning it
     * @param  string $platform
     * @param  string $device_token
     * @return SnsEndpointContract|bool
     */
    public function getEndpoint(string $platform, string $device_token){
        //try to get the endpoint for the device
        $deviceEndpoint = $this->model::forDevice($device_token, $platform);

        //if the endpoint exists return it otherwise create it
        return $deviceEndpoint->first() ?? $this->createEndpoint($platform, $device_token);
    }

    /**
     * Creates a new endpoint within sns
     * @param $device_platform
     * @param $device_token
     * @return SnsEndpointContract|bool
     */
    protected function createEndpoint($device_platform, $device_token){

        $credentials = $this->getCredentialsForPlatform($device_platform);

        //if the credentials are invalid don't attempt to create the platform
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

        //NOTES: SNS THROWS ITS OWN EXCEPTION SO MAYBE JUST TRY CATCH A COPY OF IT AND PASS DATA WITH SENTRY
        //TODO: ADD EXCEPTION FOR FAIlURE
    }

    /**
     * Gets the arn for the given platform
     * @param  string $device_platform
     * @return string|bool
     * @throws DevicePlatformDoesNotExistException|PlatformArnNotValidException
     */
    protected function getCredentialsForPlatform(String $device_platform){
        //if the platform exists return its arn from the config
        $platform_arn = config(
            'sns.platform_arns.' .
            Str::lower($device_platform)
        );

        if($platform_arn === null){
            throw new DevicePlatformDoesNotExistException("Device platform does not exist or isn't configured");
        }

        $platform = $this->validate(
            [ 'platform_arn' => 'required|array|size:6' ],
            [ 'platform_arn' => explode(':', $platform_arn) ]
        )
            ? $platform_arn
            : false;

        if(!$platform){
            //TODO: Add sentry details for platform arn here
            throw new PlatformArnNotValidException("Platform Arn {$platform_arn} is not valid");
        }

        return $platform;
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

        //NOTES: SNS HAS ITS OWN EXCEPTION
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
