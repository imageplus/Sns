<?php


namespace Imageplus\Sns\Controllers;


use Illuminate\Support\Collection;
use Imageplus\Sns\Requests\SnsRemoveDeviceRequest;
use Imageplus\Sns\Resources\SnsTopicSubscriptionResource;
use Illuminate\Support\Facades\Auth;
use Imageplus\Sns\Facades\Sns;
use Imageplus\Sns\Requests\SnsAddDeviceRequest;
use Illuminate\Http\Request;

/**
 * Class SnsController
 * @package Imageplus\Sns\Controllers
 * @author Harry Hindson
 */
class SnsController
{
    /**
     * Adds a new device to sns
     * @param $model_id
     * @param SnsAddDeviceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerDevice(SnsAddDeviceRequest $request, $model_id = null){

        //if there is no model id and it is set to use auth the model is the authenticated used
        if($model_id == null && config('sns.use_auth')){
            $model = Auth::user();
        } else {
            //this is not validated if the model_id is not null as the route will throw a 404
            //if the parameter is not present and use auth is false

            //this needs to have a model to attach too so find it or throw a 404
            $model = config('sns.default_model')::findOrFail($model_id);
        }

        //Register the device in sns
        $subscriptions = Sns::registerDevice(
            $model,
            $request->get('device_token'),
            $request->get('platform'),
            $request->get('reset', false)
        );

        //if the model isn't set the subscription failed so throw the errors back
        if(!$subscriptions){
            return response()->json([
                'message' => 'Device Registration Failed',
                //contains the validator errors
                'errors' => Sns::getErrors()
            ], 422);
        }

        //the subscription was successful so return the arn
        return response()->json([
            'subscriptions' => SnsTopicSubscriptionResource::collection($subscriptions),
            'message' => 'Device Added Successfully'
        ]);
    }

    /**
     * Remove a device from sns
     * @param $value
     * @return \Illuminate\Http\JsonResponse
     */
    public function unregisterDevice(SnsRemoveDeviceRequest $request){
        $removedSubscription = Sns::unregisterDevice([
            $request->get('device_token'),
            $request->get('platform')
        ]);

        if(!$removedSubscription){
            return response()->json([
                'message' => 'Failed To Unregister Device',
            ], 422);
        }

        return response()->json([
            'message' => 'Device Removed Successfully'
        ]);
    }
}
