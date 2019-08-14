<?php

// TODO: Use new db statement structure
class ChargeBee extends ApiObject {
    
    /* -------- TABLES (T) AND VIEWS (V) -------- */
    private $t_main = "auth";

    /* ----------- PUBLIC BASIC PARAMS ---------- */
    protected $keys = [];

    /* ------------------ INIT ------------------ */
    public function __construct($db, $account = false) {
        ChargeBee_Environment::configure(Env_bill::cb_site, Env_bill::cb_tkn);
        $this->db = $db;
        if ($account) $this->setAccount($account);
        else $this->setAccount();
    }

    /* ----------------- METHODS ---------------- */

    public function subscription($subID) {

        $result = ChargeBee_Subscription::retrieve($subID);
        $sub = $result->subscription();

        return (object) [
            "user_id" => $sub->customerId,
            "status" => $sub->status,
            "deleted" => $sub->deleted,
            "subscription" => $sub->id,
            "expiration_stamp" => $sub->currentTermEnd,
            "plan" => $sub->planId
        ];

    }

    public function cbCheckout($token) {
        $result = ChargeBee_HostedPage::retrieve($token);
        $hostedPage = $result->hostedPage()->getValues();
        return (object) $hostedPage['content'];
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
    
        return $result->hostedPage()->getValues();

    }

    public function newPortal() {

        $result = ChargeBee_PortalSession::create([
            "customer" => [
                "id" => $this->user->id
            ]
        ]);

        return $result->portalSession()->getValues();

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