<?php

namespace libraries;

use middleware\CrmMiddleware;

class RouterV2
{
    public $app;

    public function __construct()
    {
        $controller = new ControllerV2();
        $this->app = $controller->routes();
        $jwtMiddleware = $controller->jwtMiddleWare();
        $crmMiddleware = new CrmMiddleware();

        $this->app->group('/v2', function ($app) use ($jwtMiddleware, $crmMiddleware) {

            // Applicant List Endpoint
            $app->group('/internal/application', function ($app) use ($jwtMiddleware) {
                $app->get('/list/searchby', 'controllers\\V2\\Internal\\ApplicantList:searchList')->add($jwtMiddleware);
                $app->get('/list', 'controllers\\V2\\Internal\\ApplicantList:reviewList')->add($jwtMiddleware);
                $app->get(
                    '/list/reviewed',
                    'controllers\\V2\\Internal\\ApplicantList:reviewedList'
                )->add($jwtMiddleware);
                $app->get('/detail', 'controllers\\V2\\Internal\\ApplicantList:details')->add($jwtMiddleware)->setName('by-detail-ID');
                $app->get('/detailgohalal', 'controllers\\V2\\Internal\\ApplicantList:details')->add($jwtMiddleware)->setName('by-gohalal-ID');
                $app->get('/detail-app', 'controllers\\V2\\Internal\\ApplicantList:details')->add($jwtMiddleware)->setName('by-applicant-ID');
                $app->get('/doc', 'controllers\\V2\\Internal\\ApplicantList:getDoc');
                $app->post('/mark', 'controllers\\V2\\Internal\\ApplicantList:mark')->add($jwtMiddleware);
                //getProductList
                $app->get('/productlist', 'controllers\\V2\\Internal\\ApplicantList:getPFList');
                $app->post('/savePFDetails', 'controllers\\V2\\Internal\\ApplicantList:updatePFDetails')->add($jwtMiddleware);
            });

            $app->group('/internal/application/v3', function ($app) use ($jwtMiddleware) {
                $app->get('/list', 'controllers\\V2\\Internal\\ApplicantList:reviewListV3')->add($jwtMiddleware);
                $app->get(
                    '/list/reviewed',
                    'controllers\\V2\\Internal\\ApplicantList:reviewedListV3'
                )->add($jwtMiddleware);
            });

            $app->get(
                '/internal/export[/{type:.*}]',
                'controllers\\V2\\Internal\\ExportData:doExport'
            )->add($jwtMiddleware);


            // Bank CRM Endpoint
            $app->group('/bank-crm', function ($app) use ($jwtMiddleware, $crmMiddleware) {
                // List Application For Bank CRM, Support Pagination.
                $app->post('/list-application', 'controllers\\V2\\CRM\\ApplicantList:lists')->add(new CrmMiddleware("pf_application.view"));
                // List Application For Bank CRM, Support Pagination. Go-Halal
                $app->post('/list-application-gohalal', 'controllers\\V2\\CRM\\ApplicantList:listsGohalal')->add(new CrmMiddleware("pf_application.view"));
                // Get Applicant Detail by detail_id value
                $app->post('/applicant/detail', 'controllers\\V2\\CRM\\ApplicantList:details')->setName('crm-app-detail')->add(new CrmMiddleware("pf_application.view"));
                // Change Applicant Status By detail_id. (support multiple id)
                $app->post('/applicant/change-status', 'controllers\\V2\\CRM\\ApplicantList:changeStatus')->add(new CrmMiddleware("pf_application.edit"));
                ;
                
                //Change Password
                $app->post('/change-password', 'controllers\\V2\\CRM\\ApplicantList:changePassword');

                // produk
                $app->post('/produk/pf', 'controllers\\V2\\CRM\\ProductList:productPf');
                $app->post('/produk/cc', 'controllers\\V2\\CRM\\ProductList:productCc');
                // end of IF-557
                // Bulk Upload Excel Lead
                $app->post('/applicant/bulk-upload', 'controllers\\V2\\CRM\\ImportData:upload');
                $app->post('/applicant/bulk-upload-gohalal', 'controllers\\V2\\CRM\\ImportData:uploadGoHalal');
                $app->get('/applicant/running-upload-gohalal', 'controllers\\V2\\CRM\\ImportData:runningUploadGoHalal');
                // Bank Lead SLA Reminder
                $app->get('/send-reminder', 'controllers\\V2\\CRM\\SLA:sendReminder');
            });

            // Bank CRM Analytics Endpoint
            $app->group('/bank-crm/analytics', function ($app) use ($jwtMiddleware) {
                // Applicant Status
                $app->get('/applicant-status', 'controllers\\V2\\CRM\\Analytics:appStatus');
                // Applicant Amount
                $app->get('/applicant-amount', 'controllers\\V2\\CRM\\Analytics:financialAmount');
                // Applicant Sector
                $app->get('/applicant-sector', 'controllers\\V2\\CRM\\Analytics:appSector');
                // Applicant Gender
                $app->get('/applicant-gender', 'controllers\\V2\\CRM\\Analytics:appGender');
                // Applicant Status Trend
                $app->get('/status-trend', 'controllers\\V2\\CRM\\Analytics:statusTrend');
            });


            // Applicant With CRA status Lists
            $app->group('/internal/cra', function ($app) use ($jwtMiddleware) {
                $app->get('/pass-list', 'controllers\\V2\\Internal\\CRAList:craPassLists')->add($jwtMiddleware);
                $app->get('/fail-list', 'controllers\\V2\\Internal\\CRAList:craFailLists')->add($jwtMiddleware);
            });

            // Applicant AFF
            $app->group('/affiliate/application', function ($app) use ($jwtMiddleware) {
                $app->post('/list', 'controllers\\V2\\AFF\\ApplicantList:lists'); //->add($jwtMiddleware);
            });

            // Affiliate Endpoint
            $app->group('/affiliate/applicant', function ($app) use ($jwtMiddleware) {
                $app->get('/list', 'controllers\\V2\\Affiliate\\ApplicantList:lists')->add($jwtMiddleware);
            });

            // Affiliate Data Analytics V2
            $app->group('/affiliate/analytics', function ($app) use ($jwtMiddleware) {
                $app->get('/daily', 'controllers\\V2\\Affiliate\\AffiliateAnalytics:getAnalyticsDaily')->add($jwtMiddleware);
                $app->get('/weekly', 'controllers\\V2\\Affiliate\\AffiliateAnalytics:getAnalyticsWeekly')->add($jwtMiddleware);
                $app->get('/monthly', 'controllers\\V2\\Affiliate\\AffiliateAnalytics:getAnalyticsMonthly')->add($jwtMiddleware);
            });

            $app->get('/test-pdf/{appID}', 'controllers\\V2\\Demo\\Prefilled:render');

            $app->get('/industry-list', 'controllers\\V2\\Misc\\IndustryList:getList');


            $app->group('/internal/application-v3', function ($app) use ($jwtMiddleware) {
                // Status filter = {new, submitted, unsuccessful}
                $app->get(
                    '/list/[{statusFilter:.*}]',
                    'controllers\\V2\\Internal\\ApplicantList:applicantListV3'
                )->add($jwtMiddleware);
            });

            // Dropoff Applicant List Endpoint V3
            $app->group('/internal/dropoff-v3', function ($app) use ($jwtMiddleware) {
                // Status filter = {all, verified, unverified, unmatch}
                $app->get(
                    '/[{statusFilter:.*}]',
                    'controllers\\V2\\Internal\\DropoffList:dropoffListsV3'
                )->add($jwtMiddleware);
            });


            $app->get('/sector-list', 'controllers\\V2\\Misc\\SectorList:getList');
            $app->get('/job-list', 'controllers\\V2\\Misc\\JobStatusList:getList');

            $app->group('/gohalal', function ($app)  use ($jwtMiddleware) {
                $app->post('/crm/list-application', 'controllers\\V2\\GoHalal\\ApplicantList:lists')->add(new CrmMiddleware("pf_application.view"));
                $app->post('/crm/gs-list-application', 'controllers\\V2\\GoHalal\\ApplicantList:gsLists')->add(new CrmMiddleware("pf_application.view"));
                $app->post('/internal/list-application', 'controllers\\V2\\GoHalal\\ApplicantList:internalLists')->add($jwtMiddleware);
                $app->get('/internal/list-payment-file', 'controllers\\V2\\GoHalal\\UploadPaymentList:internalLists')->add($jwtMiddleware);
                $app->post('/internal/list-payment-file', 'controllers\\V2\\GoHalal\\UploadPaymentList:internalLists')->add($jwtMiddleware);
                $app->post('/internal/detail-payment-file', 'controllers\\V2\\GoHalal\\UploadPaymentList:details')->add($jwtMiddleware);
                $app->post('/crm/detail', 'controllers\\V2\\GoHalal\\ApplicantList:details')->setName('crm-app-detail')->add(new CrmMiddleware("pf_application.view"));
                $app->post('/crm/bulk-upload', 'controllers\\V2\\CRM\\ImportData:uploadGoHalal');
                $app->post('/crm/disbursed-upload', 'controllers\\V2\\CRM\\ImportData:uploadGoHalalDisbursed');
                $app->post('/internal/payment-upload', 'controllers\\V2\\CRM\\ImportData:uploadGoHalalPayment')->add($jwtMiddleware);
                $app->get('/get-reviewed', 'controllers\\V2\\GoHalal\\GoHalalController:getApplicant');
                $app->get('/crm/dashboard', 'controllers\\V2\\GoHalal\\ApplicantList:dashboardGohalal')->add(new CrmMiddleware("pf_application.view"));
                $app->post('/crm/detail-validate', 'controllers\\V2\\GoHalal\\GoHalalController:ValidationDetail');
                $app->post('/crm/save-validate', 'controllers\\V2\\GoHalal\\GoHalalController:ValidationUpdate');
                // Confirm Change Applicant Status By detail_id and app_id.
                $app->post('/crm/confirm-change-status', 'controllers\\V2\\GoHalal\\GoHalalController:confirmChangeStatus')->add(new CrmMiddleware("pf_application.edit"));
                ;
                $app->get(
                    '/completed_tawarruq/export[/{type:.*}]',
                    'controllers\\V2\\GoHalal\\ExportData:doExport'
                );
                $app->get(
                    '/excel/export[/{type:.*}]',
                    'controllers\\V2\\GoHalal\\ExportData:doExcelExport'
                );
                $app->get(
                    '/excel/export_fwd_approved[/{type:.*}]',
                    'controllers\\V2\\GoHalal\\ExportData:doExcelFwdExport'
                );
                $app->get('/crm/dropbox', 'controllers\\V2\\GoHalal\\DropboxDownload:dbx_get_file');
                $app->get('/crm/dropbox2', 'controllers\\V2\\GoHalal\\DropboxDownload:download');
                $app->get('/crm/dropboxlist', 'controllers\\V2\\GoHalal\\DropboxDownload:listFolder');
                $app->get('/crm/dropboxcreate', 'controllers\\V2\\GoHalal\\DropboxDownload:createFolder');
                $app->get('/crm/dropboxmove', 'controllers\\V2\\GoHalal\\DropboxDownload:moveFiles');
                // Status filter = {new, submitted, unsuccessful}
                $app->get(
                    '/list/[{statusFilter:.*}]',
                    'controllers\\V2\\GoHalal\\ApplicantList:applicantListInternal'
                    // 'controllers\\V2\\Internal\\ApplicantList:applicantListV3'
                )->add($jwtMiddleware);
                $app->post('/crm/tawarruq-token', 'controllers\\V2\\GoHalal\\GoHalalController:tawarruqLink');
            });
        });




        // CORS handler
        $this->app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($req, $res) {
            $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
            return $handler($req, $res);
        });

        $this->app->run();
    }
}
