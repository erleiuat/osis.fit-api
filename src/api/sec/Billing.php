<?php

// TODO: Use new db statement structure
class Billing extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "user_subscription";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = [];

    /* ------------------ INIT ------------------ */
    public function __construct($db, $user = false) {
        ChargeBee_Environment::configure(Env_bill::cb_site, Env_bill::cb_tkn);
        $this->db = $db;
        if ($user) $this->setUser($user);
        else $this->setUser();
    }

    /* ----------------- METHODS ---------------- */

    public function cbSubscription($subID) {

        $result = ChargeBee_Subscription::retrieve($subID);
        $sub = $result->subscription();

        $subObj = (object) [
            "id" => $sub->id,
            "user_id" => $sub->customerId,
            "plan" => $sub->planId,
            "quantity" => $sub->planQuantity,
            "status" => $sub->status,
            "deleted" => $sub->deleted,
        ];

        return $subObj;

    }

    public function cbCheckout($token) {
        $result = ChargeBee_HostedPage::retrieve($token);
        $hostedPage = $result->hostedPage()->getValues();
        $info = (object) $hostedPage['content'];
        return $info;
    }

    public function newCheckout($uInfo) {

        $result = ChargeBee_HostedPage::checkoutNew([
            "subscription" => [
                "planId" => Env_bill::plan
            ], 
            "customer" => [
                "email" => $this->user->mail, 
                "firstName" => $uInfo->firstname, 
                "lastName" => $uInfo->lastname,
                "id" => $this->user->id
            ],
            "billingAddress" => [
                "firstName" => $uInfo->firstname, 
                "lastName" => $uInfo->lastname
            ]
        ]);
    
        $hostedPage = $result->hostedPage();
        $output = $hostedPage->getValues();

        return $output;

    }

    public function newPortal() {

        $result = ChargeBee_PortalSession::create([
            "customer" => [
                "id" => $this->user->id
            ]
        ]);

        $portalSession = $result->portalSession();
        $output = $portalSession->getValues();

        return $output;

    }

    public function readUser() {

        $where = ['user_id' => $this->user->id];
        $result = $this->db->makeSelect($this->t_main, $where);

        if (count($result) > 1) throw new ApiException(500, 'too_many_found', get_class($this));

        if (count($result) === 1) return (object) [
            "user_id" => $this->user->id,
            "subscription" => $result[0]['subscription_id'],
            "plan" => $result[0]['plan_id'],
            "active" => $result[0]['active']
        ];
        else return (object) [
            "user_id" => $this->user->id,
            "subscription" => false,
            "plan" => false,
            "active" => false
        ];

    }

    public function hasPremium(){

        $plan = false;
        $active = false;
        $subscription = false;

        $user = $this->readUser();
        
        if ($user->subscription) {

            $sub = $this->cbSubscription($user->subscription);

            if ($sub->user_id !== $user->user_id) throw new ApiException(500, 'subscription_user_mismatch', get_class($this));

            $plan = $sub->plan;
            $subscription = $sub->id;
            if (!$sub->deleted) {
                if($sub->status === 'active' || $sub->status === 'non_renewing') $active = true;
            } 

        }

        return (object) [
            "active" => $active,
            "subscription" => $subscription,
            "plan" => $plan
        ];

    }

    public function setSub($info) {

        $vals = [
            'user_id' => $this->user->id, 
            'subscription_id' => $info->subscription,
            'plan_id' => $info->plan,
            'active' => $info->active
        ];

        $changed = $this->db->makeReplace($this->t_main, $vals);

        if ($changed < 1) throw new ApiException(500, 'nothing_changed', get_class($this));
        if ($changed > 2) throw new ApiException(500, 'too_many_changed', get_class($this));

        return $this;

    }

}