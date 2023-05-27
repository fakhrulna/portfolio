<?php

namespace helpers\Constants;

class StatusTagCons
{
    const STATUS_APPROVED = "approved";
    const TAG_APPROVED = "APPROVED";

    const STATUS_REJECTED = "rejected";
    const TAG_REJECTED = "REJECTED";

    const STATUS_INTERNAL_REJECTED = "internal_rejected";
    const TAG_INTERNAL_REJECTED = "UNSUCCESSFUL";

    const STATUS_INTERNAL_REVIEW = "internal_review";
    const STATUS_INTERNAL_REVIEWED = "internal_reviewed";
    const TAG_INTERNAL_REVIEWED = "SUBMITTED";

    const STATUS_CRA_PASS = "cra_pass";
    const STATUS_CRA_FAIL = "cra_fail";
    const TAG_CRA_PASS = "SUBMITTED";

    const STATUS_NOT_INTERESTED = "not_interested";
    const TAG_NOT_INTERESTED = "CANCELLED";

    const STATUS_APPLIED = "applied";
    const STATUS_VERIFIED_OTP = "verified_otp";
    const STATUS_CREATED = "created";
    const STATUS_INTERNAL_KIV = "internal_kiv";

    const TAG_IN_REVIEW = "IN REVIEW";
    const TAG_NEW = "NEW";

    // DROP OFF
    const LAST_PAGE_PERSONAL_DETAIL = "personal_detail";
    const LAST_PAGE_OTP = "otp";
    const LAST_PAGE_PRODUCT_SEARCH = "product-search";
    const LAST_PAGE_JOB = "job";
    const LAST_PAGE_PRODUCT_SUBMIT = "product-submit";
    const LAST_PAGE_INCOMPLETE = null;


    const TAG_DROPOFF_UNVERIFIED = "DROPOFF - UNVERIFIED";
    const TAG_DROPOFF_VERIFIED = "DROPOFF - VERIFIED";
    const TAG_DROPOFF_UNMATCHED = "DROPOFF - UNMATCHED";

    const REASON_PAGE_PERSONAL_DETAIL = "Pending Verification";
    const REASON_PAGE_OTP = "Incomplete job details";
    const REASON_PAGE_PRODUCT_SEARCH = "No Product Selected";
    const REASON_PAGE_JOB = "No Match";
    const REASON_PAGE_PRODUCT_SUBMIT = "Dropoff CRA";
    const REASON_PAGE_INCOMPLETE = "Incomplete personal information";


}