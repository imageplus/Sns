<?php


namespace Imageplus\Sns\Controllers;


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
    public function addDevice(SnsAddDeviceRequest $request, $model_id = null){

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
        $subscription_model = Sns::registerDevice(
            $model,
            $request->get('device_token'),
            $request->get('platform'),
            $request->get('reset', false)
        );

        //if the model isn't set the subscription failed so throw the errors back
        if(!$subscription_model){
            return response()->json([
                'message' => 'Device Registration Failed',
                //contains the validator errors
                'errors' => Sns::getErrors()
            ], 422);
        }

        //the subscription was successful so return the arn
        return response()->json([
            'SubscriptionArn' => $subscription_model->subscription_arn,
            'message' => 'Device Added Successfully'
        ]);
    }

    /**
     * Remove a device from sns
     * @param $value
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDevice($value){
        //this can be either the subscriptionArn of an id of the subscription model
        //so try to find it from the model
        $model = config('sns.models.subscription')::find($value);

        Sns::unregisterDevice(
            //if the model exists use that otherwise it must be an arn
            $model ? $model : $value
        );

        return response()->json([
            'message' => 'Device Removed Successfully'
        ]);
    }

    /**
     * Remove a topic from sns (remove all data for a model)
     * @param $value
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeTopic($value, Request $request){
        //if there is a model given assume that otherwise use the default
        $model = $request->get('model', config('sns.default_model'));

        //find the model
        $model = $model::find($value);

        Sns::unregisterTopic(
            //if the model exists return its topic otherwise the value must be a topic
            $model ? $model->sns_topic : $value
        );

        return response()->json([
            'message' => 'Topic Removed Successfully'
        ]);
    }
}
