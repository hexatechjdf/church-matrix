<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Contact;
use App\Models\Locations;

use App\Models\User;
use stdClass;

class PlanningCenterController extends Controller
{

    public function callback(Request $request)
    {
        $code = $request->code;
        $data = get_planning_token($code);
        
        $loc_id=$request->location_id;
   
        if ($data && property_exists($data, 'error')) {
            return redirect()->route('auth.check')->withError($data->error_description);
        }
        $res_me = planning_api_call('people/v2', 'get', '', [], false, $data->access_token);
        
       // save_logs($res_me,'me');
        
        if($res_me && property_exists($res_me,'data')){
            
            $org_id = $res_me->data->id;
            $org_id_name='-';
            try{
                $org_id_name = $res_me->data->attributes->name;
            }catch(\Exception $e){
                
            }

        $res = Setting::where('key', 'planning_organization_id')->where('value',$org_id)->where('location_id','<>',$loc_id)->first();
        if (!is_null($res) && !empty($res->value)) {

            echo 'Unable to connect Organization already connect with '.get_setting($res->location_id, 'ghl_location_id');
            die;
        }
        save_setting('planning_access_token', $data->access_token, $loc_id);
        save_setting('planning_refresh_token', $data->refresh_token, $loc_id);
        save_setting('planning_organization_id', $org_id, $loc_id);
         save_setting('planning_organization_name', $org_id_name, $loc_id);
        
            
        }else{
            
            die('Unable to get organization id');
            
        }
        
         $webhooks = ['people.v2.events.email.created','people.v2.events.person.created','people.v2.events.phone_number.created'];
        try{
            foreach($webhooks as $t)
            {
            $obj = new \stdClass;
            $dataobj = new stdClass;
            $dataobj->attributes = new \stdClass;
            $dataobj->attributes->name = $t;
             $dataobj->attributes->url = route('planning_lead_capture');
            $obj->data= $dataobj;
             $webhook = planning_api_call('webhooks/v2/subscriptions', 'POST', json_encode($obj), [], false, $data->access_token);
            save_logs($webhook,'webhook');
            
            }
        }catch(\Exception $e){
            save_logs($e->getMessage(),'error');
        }
        
       
        


        return view('planning-done');
    }

    public function getContact()
    {
        $res = planning_api_call('people/v2/emails', 'GET', '', [], true);

       
    }

    public function crm_lead_capture(Request $lead)
    {
     
         try {
             
              if($lead->type!='ContactCreate')
            {
                exit;
            }
             
          save_logs($lead->all(),'crm_respon');
        // sleep(2);
        $user = User::where('location', $lead->locationId)->first();
        if (!$user) {
            return;
        }
        request()->user_id = $user->id;
        request()->location = $lead->locationId;
        $linked = 'people/v2/';
        $email_rs = new \stdClass;
                $phone_rs = new \stdClass;
                $address_rs = new \stdClass;
        // $contact = Contact::where('contact_id', $lead['id'])->first();
        $contact = Contact::where('contact_id', $lead->id)->first();
        if (!is_null($contact)) {

            if ($contact->name != $lead['firstName'] || $contact->email != $lead['email'] || $contact->phone != $lead['phone'] || $contact->address != $lead['address1']) {

                $contact->people_id = $contact->people_id;
                $contact->name = $lead->firstName ?? '';
                $contact->email = $lead->email ?? '';
                $contact->address = $lead->address1 ?? '';
                $contact->phone = $lead->phone ?? '';
                $contact->save();
                // dd('Saved in db');
               
                $perosn = [
                    'data' => [
                        'type' => 'Person',
                        'attributes' => [
                            'first_name' => $lead->firstName ?? '',
                            'last_name' => $lead->lastName ?? '',
                        ]
                    ]
                ];
                $person_rs = planning_api_call($linked . 'people' . $contact->people_id, 'PATCH', json_encode($perosn), [], true);
                // dd($person_rs, $contact->people_id);
                if ($person_rs &&  property_exists($person_rs, 'data')) {
                    $emailr = $lead->email ?? '';
                    if (!empty($emailr)) {
                        $email = [
                            'data' => [
                                'type' => 'Email',
                                'id' => $contact->people_id,
                                'attributes' => [
                                    'address' => $emailr,
                                    'location' => 'Home',
                                    "primary" => true,
                                ]
                            ]
                        ];
                        $email_rs = planning_api_call($linked . 'emails/' . $contact->email_id, 'PATCH', json_encode($email), [], true);
                    }


                    $phoner = $lead->phone ?? '';
                    if (!empty($phoner)) {
                        $phone = [
                            'data' => [
                                'type' => 'PhoneNumber',
                                'id' => $contact->people_id,
                                'attributes' => [
                                    'number' => $lead->phone ?? '',
                                    'location' => 'Home',
                                    "primary" => true,
                                ]
                            ]
                        ];
                        $phone_rs = planning_api_call($linked . 'phone_numbers/' . $contact->phone_id, 'PATCH', json_encode($phone), [], true);
                    }






                    $city = $lead->city ?? '';
                    $state = $lead->state ?? '';
                    $zip = $lead->postalCode ?? '';
                    $address1 = $lead->address1 ?? '';
                    if (!empty($city) || !empty($state) || !empty($zip) || !empty($address1)) {

                        $addressfields = [];
                        if (!empty($city)) {
                            $addressfields['city'] = $city;
                        }
                        if (!empty($state)) {
                            $addressfields['state'] = $state;
                        }
                        if (!empty($zip)) {
                            $addressfields['zip'] = $zip;
                        }
                        if (!empty($address1)) {
                            $addressfields['street'] = $address1;
                        }

                        $address = [
                            'data' => [
                                'type' => 'Address',
                                'id' => $contact->people_id,
                                'attributes' => $addressfields
                            ]
                        ];

                        $address_rs = planning_api_call($linked . 'addresses/' . $contact->address_id, 'PATCH', json_encode($address), [], true);
                    }


                    if (($email_rs &&  property_exists($email_rs, 'data')) || ($address_rs &&  property_exists($address_rs, 'data')) || ($phone_rs &&  property_exists($phone_rs, 'data'))) {
                        echo 'Contact has been Updated.';
                        exit;
                    }
                }
            }
            echo 'No Changes Found in this Contact';
        } else {


            $json = [
                'data' => [
                    'type' => 'Person',
                    'attributes' => [
                        'first_name' => $lead->firstName ?? '',
                        'last_name' => $lead->lastName ?? '',
                    ]
                ]
            ];
            save_logs($json,'planning_person_data');
            $person_res = planning_api_call($linked . 'people', 'POST', json_encode($json), [], true);
            
            save_logs($person_res,'planning_person');
            if ($person_res &&  property_exists($person_res, 'data')) {
                $linked .= 'people/';
                // dd($person_res);
                $person_id = $person_res->data->id;
                $email = $lead->email ?? '';
                if (!empty($email)) {
                    $email = [
                        'data' => [
                            'type' => 'Email',
                            'id' => $person_id,
                            'attributes' => [
                                'address' => $email,
                                'location' => 'Home',
                                'primary' => true,


                            ]
                        ]
                    ];
                    $email_rs = planning_api_call($linked . $person_id . '/emails', 'POST', json_encode($email), [], true);
                }

                $workflow_selected =  get_setting(request()->user_id, 'workflow_selected');
                if (!empty($workflow_selected)) {
                    $workflow = [
                        'data' => [
                            'type' => 'WorkflowCard',
                            'id' => $workflow_selected,
                            'attributes' => [
                                'person_id' => $person_id ?? '',

                            ]
                        ]
                    ];
                    $workflow = planning_api_call('people/v2/workflows/' . $workflow_selected . '/cards', 'POST', json_encode($workflow), [], true);
                }

                $phone = $lead->phone ?? '';
                if (!empty($phone)) {
                    $phone = [
                        'data' => [
                            'type' => 'PhoneNumber',
                            'id' => $person_id,
                            'attributes' => [
                                'number' => $phone,
                                'carrier' => '',
                                'location' => 'Home',
                                'primary' => true,
                            ]
                        ]
                    ];
                    $phone_rs = planning_api_call($linked .  $person_id . '/phone_numbers', 'POST', json_encode($phone), [], true);
                }

                $city = $lead->city ?? '';
                $state = $lead->state ?? '';
                $zip = $lead->postalCode ?? '';
                $address1 = $lead->address1 ?? '';
                if (!empty($city) || !empty($state) || !empty($zip) || !empty($address1)) {
                    $address = [
                        'data' => [
                            'type' => 'Address',
                            'id' => $person_id,
                            'attributes' => [
                                'city' => $city,
                                'state' => $state,
                                'zip' => $zip,
                                'street' => $address1,
                                'location' => 'Home',
                                'primary' => true,

                            ]
                        ]
                    ];

                    $address_rs = planning_api_call($linked . $person_id . '/addresses', 'POST', json_encode($address), [], true);
                }

                if ($email_rs &&  property_exists($email_rs, 'data')) {



                    if ($phone_rs &&  property_exists($phone_rs, 'data')) {
                    }
                }
            }
            // Now Save In Database
            $contact = new Contact();
            $contact->contact_id = $lead->id;
            $contact->people_id = $person_res->data->id;
            $contact->name = $lead->firstName  ?? '';
            $contact->email = $lead->email ?? '';
            $contact->email_id = $email_res->data->id ?? '';
            $contact->address = $lead->address1 ?? '';
            $contact->address_id = $address_res->data->id ?? '';
            $contact->phone = $lead->phone ?? '';
            $contact->phone_id = $phone_res->data->id ?? '';
            $contact->save();

            echo 'Contact Saved & Created in Planning Center as well.';
        }
    } catch (\Throwable $th) {
        echo $th->getMessage().'- '.$th->getLine();
       save_logs($th->getMessage(),'crm');
    }
    }


    public function planning_lead_capture(Request $lead)
    {
        
       
    try {
        
         

        sleep(2);
        $dt = json_decode($lead->data[0]['attributes']['payload']);
         save_logs($lead->all());
        $org_id = $lead->data[0]['relationships']['organization']['data']['id'];

        
        if ($dt->data->type == 'Person') {

            $res = Setting::with('location')->where(['key' => 'planning_organization_id', 'value' => $org_id])->first();
            //\DB::table('logs')->insert(['message'=>$res]);


            if (!$res) {
                echo 'Unable To connect';
                return;
            }
            request()->user_id = $res->location_id;
            request()->location  = $res->location->location;
            $perosn_id = $dt->data->id;
            $name = $dt->data->attributes->name;
            $email = '';
            $email_id = '';
            $phone = '';
            $phone_id = '';
            $address = '';
            $address_id = '';


            // Links For Other Attributes
            $links = $dt->data->links;
            $address_link = $links->addresses;
            $email_link = $links->emails;
           // dd($email_link);
            $phone_link = $links->phone_numbers;
            // Fetch Data from these links 
            $linked = 'people/v2/';
            $phone_link = $linked . explode($linked, $phone_link)[1];
           
            $phone_api_res = planning_api_call($phone_link);
            $email_link = $linked . explode($linked, $email_link)[1];

            $email_api_res = planning_api_call($email_link);

            $address_link = $linked . explode($linked, $address_link)[1];
            $address_api_res = planning_api_call($address_link);

            if ($phone_api_res && property_exists($email_api_res, 'data')  && !empty($phone_api_res->data)) {

                $phone_id = $phone_api_res->data[0]->id;
                $phone = $phone_api_res->data[0]->attributes->number;
            } else {
                $phone_id = '';
                $phone = '';
            }
            if ($email_api_res && property_exists($email_api_res, 'data')  && !empty($email_api_res->data)) {

                $email_id = $email_api_res->data[0]->id;
                $email = $email_api_res->data[0]->attributes->address;
            } else {
                $email_id = '';
                $email = '';
            }
            if ($address_api_res && property_exists($address_api_res, 'data')  && !empty($address_api_res->data)) {

                $address_id = $address_api_res->data[0]->id;
                $address = $address_api_res->data[0]->attributes->city;
            } else {
                $address_id = '';
                $address = '';
            }



            $con = Contact::where('people_id', $perosn_id)->first();


            if (!is_null($con)) {

                if ($con->name != $name || $con->email != $email || $con->phone != $phone || $con->address != $address) {


                    // So here we got some changes lets update
                    $con->people_id = $perosn_id;
                    $con->name = $name ?? '';
                    $con->email = $email ?? '';
                    $con->address = $address ?? '';
                    $con->phone = $phone ?? '';
                    $con->save();

                    // Now update it on GHL Side Too
                    $contact = new stdClass;
                    $contact->name = $name;
                    $contact->email = $email;
                    $contact->phone = $phone;
                    $contact->address1 = $address;
                    $contacts = json_encode($contact);
                    $response = ghl_api_call('contacts/' . $con->contact_id, 'PUT', $contacts, [], true, true);
                    save_logs($response);
        
                    echo 'Contact is Updated in CRM & database.';
                    exit;
                } else {
                    echo 'No Changes Found in this lead.';
                    exit;
                }
            } else {



                $contact = new stdClass;
                $contact->name = $name;
                $contact->email = $email;
                $contact->phone = $phone;
                $contact->locationId = $res->location->location;
                $contact->address1 = $address;
                $contact->tags=['PCO'];
                $contacts = json_encode($contact);
                
                // save_logs($contacts);
                $response = ghl_api_call('contacts/', 'POST', $contacts, [], true, true);
                if ($response && property_exists($response, 'contact')) {
                    $ghl_lead_res = $response->contact;
                    $contact = new Contact();
                    $contact->contact_id = $ghl_lead_res->id;
                    $contact->people_id = $perosn_id;
                    $contact->name = $name ?? '';
                    $contact->email = $email ?? '';
                    $contact->email_id = $email_id ?? '';
                    $contact->address = $address ?? '';
                    $contact->address_id = $address_id ?? '';
                    $contact->phone = $phone ?? '';
                    $contact->phone_id = $phone_id ?? '';
                    $contact->save();
                    echo 'Conatct Saved & Created in CRM.';
                } else {
                    echo 'unable to save';
                }
                save_logs($response);



                // Now save this with People Id in database

                exit;
            }
        }
        echo 'Lead type is not a Person.';
        exit;
    } catch (\Throwable $th) {
        save_logs($th->getMessage().'at line '.$th->getLine());
        

        
        exit;
    }
    }
}
