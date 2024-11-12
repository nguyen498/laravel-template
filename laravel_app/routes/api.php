<?php

use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressApiController;
use App\Http\Controllers\Api\AdvertisingRequestApiController;
use App\Http\Controllers\Api\ConfigApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\FilterApiController;
use App\Http\Controllers\Api\FeedbackApiController;
use App\Http\Controllers\Api\FilterDetailApiController;
use App\Http\Controllers\Api\MediaApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\OtpAttemptApiController;
use App\Http\Controllers\Api\RoleApiController;
use App\Http\Controllers\Api\SubCategoryApiController;
use App\Http\Controllers\Api\StateApiController;
use App\Http\Controllers\Api\UserActionPostApiController;
use App\Http\Controllers\Api\UserChatApiController;
use App\Http\Controllers\Api\UserCommentApiController;
use App\Http\Controllers\Api\UserGroupChatApiController;
use App\Http\Controllers\Api\UserInboxApiController;
use App\Http\Controllers\Api\UserMediaApiController;
use App\Http\Controllers\Api\UserPostApiController;
use App\Http\Controllers\Api\UserRecentSearchApiController;
use App\Http\Controllers\Api\UserSearchApiController;
use App\Http\Controllers\Api\OneSignalApiController;
use App\Http\Controllers\Api\PostApiController;
use App\Http\Controllers\Api\PostIndustryApiController;
use App\Http\Controllers\Api\PostAdvertisingApiController;
use App\Http\Controllers\Api\PostExcelApiController;
use App\Http\Controllers\Api\PostCmsApiController;
use App\Http\Controllers\Api\ExportExcelApiController;
use App\Http\Controllers\Api\CheckStatusApiController;
use App\Http\Controllers\Api\VisitorDetailApiController;


Route::prefix("auth")->group(function () {
    Route::prefix("user")->group(function () {
        Route::post('register', [UserApiController::class, 'register']);
        Route::post('login', [UserApiController::class, 'login'])->middleware('guest');
    });
    Route::prefix("auth")->group(function () {
        Route::post('register', [EmployeeApiController::class, 'register']);
        Route::post('login', [EmployeeApiController::class, 'login']);
    });
});


Route::middleware('auth:user')->get('/user/dashboard', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:employee')->get('/employee/dashboard', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'oneSignals', 'middleware' => ['is.auth.api']], function () {
        Route::post('/sendAll', [OneSignalApiController::class, 'sendAll']);
        Route::post('/sendToUser', [OneSignalApiController::class, 'sendToUser']);
        Route::post('/sendSegment', [OneSignalApiController::class, 'sendSegment']);
        Route::post('/getNotificationDevice', [OneSignalApiController::class, 'getNotificationDevice']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::post('/login', [UserApiController::class, 'login']);
        Route::post('/loginWithPassword', [UserApiController::class, 'loginWithPassword']);
        Route::post('/verifyOtp', [UserApiController::class, 'verifyOtp']);
        Route::post('/registerPhone', [UserApiController::class, 'registerPhone']);
        Route::post('/registerWithPassword', [UserApiController::class, 'registerWithPassword']);

        Route::post('/forgetPassword', [UserApiController::class, 'forgetPassword']);
        Route::post('/verifyResetPassword', [UserApiController::class, 'verifyResetPassword']);
        Route::post('/resendOtp', [UserApiController::class, 'resendOtp']);
        Route::post('/checkVersion', [UserApiController::class, 'checkVersion']);

        Route::group(['middleware' => ['auth.users']], function(){
            Route::post('/loginWithToken', [UserApiController::class, 'loginWithToken']);
            Route::post('/changePassword', [UserApiController::class, 'changePassword']);
            Route::post('/logout', [UserApiController::class, 'logout']);
            Route::post('/updateProfile', [UserApiController::class, 'updateProfile']);
            Route::post('/deleteByPassword', [UserApiController::class, 'deleteByPassword']);
        });

//        Route::group(['middleware' => 'auth:sanctum'], function () {
//            Route::get('/loginWithToken', [UserApiController::class, 'loginWithToken']);
//            Route::get('/findAll', [UserApiController::class, 'findAll']);
//            Route::put('/update/{id}', [UserApiController::class, 'update']);
//            Route::get('/findById/{id}', [UserApiController::class, 'findById']);
//            Route::delete('/destroy/{id}', [UserApiController::class, 'destroy']);
//            Route::post('/search', [UserApiController::class, 'search']);
//            Route::post('/dict/getDictByIds', [UserApiController::class, 'getDictByIds']);
//            Route::post('/dict/getDictByColumns', [UserApiController::class, 'getDictByColumns']);
//        });

        Route::group(['middleware' => 'auth.api'], function () {
            Route::post('/store-app', [UserApiController::class, 'storeApp']);
            Route::post('/searchApp', [UserApiController::class, 'searchApp']);
        });
    });


    Route::prefix("employees")->group(function () {
        Route::post('/loginWithPassword', [EmployeeApiController::class, 'login']);

        Route::middleware(['auth.employees'])->group(function(){
            Route::post('/loginWithToken', [EmployeeApiController::class, 'loginWithToken']);

            Route::post('/store', [EmployeeApiController::class, 'store']);
            Route::put('/update/{id}', [EmployeeApiController::class, 'update']);
            Route::get('/findById/{id}', [EmployeeApiController::class, 'findById']);
            Route::delete('/destroy/{id}', [EmployeeApiController::class, 'destroy']);
            Route::post('/search', [EmployeeApiController::class, 'search']);
        });
    });

    Route::prefix("addresses")->group(function () {
        Route::post('/store', [AddressApiController::class, 'store']);
        Route::put('/update/{id}', [AddressApiController::class, 'update']);
        Route::get('/findById/{id}', [AddressApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [AddressApiController::class, 'destroy']);
        Route::post('/search', [AddressApiController::class, 'search']);
    });

    Route::prefix("advertising_requests")->group(function () {
        Route::group(['middleware' => ['auth.employees']], function(){
            Route::post('/store', [AdvertisingRequestApiController::class, 'store']);
            Route::put('/update/{id}', [AdvertisingRequestApiController::class, 'update']);
            Route::get('/findById/{id}', [AdvertisingRequestApiController::class, 'findById']);
            Route::delete('/destroy/{id}', [AdvertisingRequestApiController::class, 'destroy']);
            Route::post('/search', [AdvertisingRequestApiController::class, 'search']);
            Route::post('/confirm', [AdvertisingRequestApiController::class, 'confirm']);
        });

        Route::group(['middleware' => ['auth.users']], function(){
            Route::post('/storeApp', [AdvertisingRequestApiController::class, 'storeApp']);
        });
    });

    Route::prefix("categories")->group(function () {
        Route::post('/store', [CategoryApiController::class, 'store']);
        Route::put('/update/{id}', [CategoryApiController::class, 'update']);
        Route::get('/findById/{id}', [CategoryApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [CategoryApiController::class, 'destroy']);
        Route::post('/search', [CategoryApiController::class, 'search']);
    });

    Route::prefix("configs")->group(function () {
        Route::post('/store', [ConfigApiController::class, 'store']);
        Route::put('/update/{id}', [ConfigApiController::class, 'update']);
        Route::get('/findById/{id}', [ConfigApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [ConfigApiController::class, 'destroy']);
        Route::post('/search', [ConfigApiController::class, 'search']);
    });

    Route::prefix("countries")->group(function () {
        Route::post('/store', [CountryApiController::class, 'store']);
        Route::put('/update/{id}', [CountryApiController::class, 'update']);
        Route::get('/findById/{id}', [CountryApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [CountryApiController::class, 'destroy']);
        Route::post('/search', [CountryApiController::class, 'search']);
        Route::get('/findAll', [CountryApiController::class, 'findAll']);
    });

    Route::prefix("devices")->group(function () {
        Route::post('/store', [DeviceApiController::class, 'store']);
        Route::put('/update/{id}', [DeviceApiController::class, 'update']);
        Route::get('/findById/{id}', [DeviceApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [DeviceApiController::class, 'destroy']);
        Route::post('/search', [DeviceApiController::class, 'search']);
        Route::get('/findAll', [DeviceApiController::class, 'findAll']);
    });

    Route::prefix("filters")->group(function () {
        Route::post('/store', [FilterApiController::class, 'store']);
        Route::put('/update/{id}', [FilterApiController::class, 'update']);
        Route::get('/findById/{id}', [FilterApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [FilterApiController::class, 'destroy']);
        Route::post('/search', [FilterApiController::class, 'search']);
        Route::get('/findAll', [FilterApiController::class, 'findAll']);
    });

    Route::prefix("feedbacks")->group(function () {
        Route::post('/store', [FeedbackApiController::class, 'store']);
        Route::put('/update/{id}', [FeedbackApiController::class, 'update']);
        Route::get('/findById/{id}', [FeedbackApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [FeedbackApiController::class, 'destroy']);
        Route::post('/search', [FeedbackApiController::class, 'search']);
        Route::get('/findAll', [FeedbackApiController::class, 'findAll']);
    });

    Route::prefix("filter_details")->group(function () {
        Route::post('/store', [FilterDetailApiController::class, 'store']);
        Route::put('/update/{id}', [FilterDetailApiController::class, 'update']);
        Route::get('/findById/{id}', [FilterDetailApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [FilterDetailApiController::class, 'destroy']);
        Route::post('/search', [FilterDetailApiController::class, 'findAll']);
    });

    Route::prefix("medias")->group(function () {
        Route::post('/upload', [MediaApiController::class, 'upload']);
        Route::delete('/delete/{id}', [MediaApiController::class, 'deleteMedia']);
    });

    Route::prefix("notifications")->group(function () {
        Route::post('/store', [NotificationApiController::class, 'store']);
        Route::put('/update/{id}', [NotificationApiController::class, 'update']);
        Route::get('/findById/{id}', [NotificationApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [NotificationApiController::class, 'destroy']);
        Route::post('/search', [NotificationApiController::class, 'search']);
    });

    Route::prefix("otp_attempts")->group(function () {
        Route::post('/store', [OtpAttemptApiController::class, 'store']);
        Route::put('/update/{id}', [OtpAttemptApiController::class, 'update']);
        Route::get('/findById/{id}', [OtpAttemptApiController::class, 'findById']);
        Route::post('destroy/{id}', [OtpAttemptApiController::class, 'destroy']);
        Route::post('/search', [OtpAttemptApiController::class, 'search']);
    });

    Route::group(['prefix' => 'roles'], function(){
        Route::get('/findAll', [RoleApiController::class, 'findAll']);
        Route::post('/store', [RoleApiController::class, 'store']);
        Route::put('/update/{id}', [RoleApiController::class, 'update']);
        Route::get('/findById/{id}', [RoleApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [RoleApiController::class, 'destroy']);
        Route::post('/search', [RoleApiController::class, 'search']);
    });

    Route::group(['prefix' => 'states'], function(){
        Route::get('/findAll', [StateApiController::class, 'findAll']);
        Route::post('/store', [StateApiController::class, 'store']);
        Route::put('/update/{id}', [StateApiController::class, 'update']);
        Route::get('/findById/{id}', [StateApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [StateApiController::class, 'destroy']);
        Route::post('/search', [StateApiController::class, 'search']);
    });

    Route::group(['prefix' => 'sub_categories'], function(){
        Route::get('/findAll', [SubCategoryApiController::class, 'findAll']);
        Route::post('/store', [SubCategoryApiController::class, 'store']);
        Route::put('/update/{id}', [SubCategoryApiController::class, 'update']);
        Route::get('/findById/{id}', [SubCategoryApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [SubCategoryApiController::class, 'destroy']);
        Route::post('/search', [SubCategoryApiController::class, 'search']);
    });

    Route::group(['prefix' => 'user_action_posts'], function(){
        Route::get('/findAll', [UserActionPostApiController::class, 'findAll']);
        Route::post('/store', [UserActionPostApiController::class, 'store']);
        Route::put('/update/{id}', [UserActionPostApiController::class, 'update']);
        Route::get('/findById/{id}', [UserActionPostApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [UserActionPostApiController::class, 'destroy']);
        Route::post('/search', [UserActionPostApiController::class, 'search']);
    });

    Route::group(['prefix' => 'user_chats'], function(){
//        Route::get('/findAll', [UserChatApiController::class, 'findAll']);
//        Route::post('/store', [UserChatApiController::class, 'store']);
//        Route::put('/update/{id}', [UserChatApiController::class, 'update']);
//        Route::get('/findById/{id}', [UserChatApiController::class, 'findById']);
//        Route::delete('/destroy/{id}', [UserChatApiController::class, 'destroy']);
//        Route::post('/search', [UserChatApiController::class, 'search']);

        Route::middleware(['auth.users'])->group(function() {
            Route::post('sendMessage', [UserChatApiController::class, 'sendMessage']);
            Route::post('searchByUser', [UserChatApiController::class, 'searchByUser']);
            Route::delete('deleteMessage/{id}', [UserChatApiController::class, 'deleteMessage']);
        });
    });

    Route::group(['prefix' => 'user_comments'], function(){
//        Route::group(['middleware' => ['auth:employees']], function(){
//            Route::get('/findAll', [UserCommentApiController::class, 'findAll']);
//            Route::post('/store', [UserCommentApiController::class, 'store']);
//            Route::put('/update/{id}', [UserCommentApiController::class, 'update']);
            Route::get('/findById/{id}', [UserCommentApiController::class, 'findById']);
//            Route::delete('/destroy/{id}', [UserCommentApiController::class, 'destroy']);
//            Route::post('/search', [UserCommentApiController::class, 'search']);
//        });

        Route::group(['middleware' => ['auth:users']], function(){
            Route::post('/comment_post', [UserCommentApiController::class, 'sendCommentPost']);
            Route::put('/update_comment/{id}', [UserCommentApiController::class, 'updateComment']);
            Route::delete('/delete_comment/{id}', [UserCommentApiController::class, 'destroy']);
            Route::post('/searchApp', [UserCommentApiController::class, 'search']);
        });

    });

    Route::group(['prefix' => 'user_group_chats'], function(){
//        Route::get('/findAll', [UserGroupChatApiController::class, 'findAll']);
//        Route::post('/store', [UserGroupChatApiController::class, 'store']);
//        Route::put('/update/{id}', [UserGroupChatApiController::class, 'update']);
//        Route::get('/findById/{id}', [UserGroupChatApiController::class, 'findById']);
//        Route::delete('/destroy/{id}', [UserGroupChatApiController::class, 'destroy']);
//        Route::post('/search', [UserGroupChatApiController::class, 'search']);

        Route::group(['middleware' => ['auth:users']], function(){
            Route::middleware(['auth.users'])->group(function() {
                Route::post('search_by_user', [UserGroupChatApiController::class, 'searchByUser']);
                Route::delete('delete_group_chat/{id}', [UserGroupChatApiController::class, 'deleteGroupChat']);
                Route::put('update_read_message/{id}', [UserGroupChatApiController::class, 'updateReadMessage']);
            });
        });
    });

    Route::group(['prefix' => 'user_inboxes'], function(){
//        Route::get('/findAll', [UserInboxApiController::class, 'findAll']);
//        Route::post('/store', [UserInboxApiController::class, 'store']);
//        Route::put('/update/{id}', [UserInboxApiController::class, 'update']);
//        Route::get('/findById/{id}', [UserInboxApiController::class, 'findById']);
//        Route::delete('/destroy/{id}', [UserInboxApiController::class, 'destroy']);
//        Route::post('/search', [UserInboxApiController::class, 'search']);

        Route::group(['middleware' => ['auth:users']], function(){
            Route::put('/update_read/{id}', [UserInboxApiController::class, 'updateRead']);
            Route::post('/update_read_all', [UserInboxApiController::class, 'updateReadAll']);
            Route::delete('/delete_inbox/{id}', [UserInboxApiController::class, 'deleteInbox']);
            Route::post('searchApp', [UserInboxApiController::class, 'searchApp']);
        });
    });

    Route::group(['prefix' => 'user_medias'], function(){
        Route::get('/findAll', [UserMediaApiController::class, 'findAll']);
        Route::post('/store', [UserMediaApiController::class, 'store']);
        Route::put('/update/{id}', [UserMediaApiController::class, 'update']);
        Route::get('/findById/{id}', [UserMediaApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [UserMediaApiController::class, 'destroy']);
        Route::post('/search', [UserMediaApiController::class, 'search']);
    });

    Route::group(['prefix' => 'user_posts'], function(){
        Route::get('/findAll', [UserPostApiController::class, 'findAll']);
        Route::post('/store', [UserPostApiController::class, 'store']);
        Route::put('/update/{id}', [UserPostApiController::class, 'update']);
        Route::get('/findById/{id}', [UserPostApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [UserPostApiController::class, 'destroy']);
        Route::post('/search', [UserPostApiController::class, 'search']);
    });

    Route::group(['prefix' => 'user_recent_search'], function(){
        Route::get('/findAll', [UserRecentSearchApiController::class, 'findAll']);
        Route::post('/store', [UserRecentSearchApiController::class, 'store']);
        Route::put('/update/{id}', [UserRecentSearchApiController::class, 'update']);
        Route::get('/findById/{id}', [UserRecentSearchApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [UserRecentSearchApiController::class, 'destroy']);
        Route::post('/search', [UserRecentSearchApiController::class, 'search']);
    });

    Route::group(['prefix' => 'user_search'], function(){
        Route::get('/findAll', [UserSearchApiController::class, 'findAll']);
        Route::post('/store', [UserSearchApiController::class, 'store']);
        Route::put('/update/{id}', [UserSearchApiController::class, 'update']);
        Route::get('/findById/{id}', [UserSearchApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [UserSearchApiController::class, 'destroy']);
        Route::post('/search', [UserSearchApiController::class, 'search']);
    });

    Route::group(['prefix' => 'posts'], function(){
        Route::group(['middleware' => ['auth:users']], function(){
            Route::get('/findAll', [PostApiController::class, 'findAll']);
            Route::post('/store', [PostApiController::class, 'store']);
            Route::put('/update/{id}', [PostApiController::class, 'update']);
            Route::get('/findById/{id}', [PostApiController::class, 'findById']);
            Route::delete('/destroy/{id}', [PostApiController::class, 'destroy']);
            Route::post('/search', [PostApiController::class, 'search']);
            Route::post('/searchElastic', [PostApiController::class, 'searchElastic']);
        });

        Route::group(['middleware' => ['auth:employees']], function(){
            Route::post('/import', [PostExcelApiController::class, 'import']);
        });
    });

    Route::group(['prefix' => 'postCms'], function(){
        Route::group(['middleware' => ['auth:employees']], function(){
            Route::put('/update/{id}', [PostCmsApiController::class, 'update']);
            Route::get('/findById/{id}', [PostCmsApiController::class, 'findById']);
            Route::delete('/destroy/{id}', [PostCmsApiController::class, 'destroy']);
            Route::post('/search', [PostCmsApiController::class, 'search']);
            // export excel
            Route::post('/exportExcel', [PostCmsApiController::class, 'exportExcel']);
        });
    });

    Route::group(['prefix' => 'exportExcels'], function(){
        Route::get('/export/{path}', [ExportExcelApiController::class, 'getExportFile'])
        ->where(['path' => '[a-zA-Z0-9_]+\/[a-zA-Z0-9_].*']);
    });

    Route::group(['prefix' => 'post_industries'], function(){
        Route::get('/findAll', [PostIndustryApiController::class, 'findAll']);
        Route::post('/store', [PostIndustryApiController::class, 'store']);
        Route::put('/update/{id}', [PostIndustryApiController::class, 'update']);
        Route::get('/findById/{id}', [PostIndustryApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [PostIndustryApiController::class, 'destroy']);
        Route::post('/search', [PostIndustryApiController::class, 'search']);
    });

    Route::group(['prefix' => 'post_advertising'], function(){
        Route::get('/findAll', [PostAdvertisingApiController::class, 'findAll']);
        Route::post('/store', [PostAdvertisingApiController::class, 'store']);
        Route::put('/update/{id}', [PostAdvertisingApiController::class, 'update']);
        Route::get('/findById/{id}', [PostAdvertisingApiController::class, 'findById']);
        Route::delete('/destroy/{id}', [PostAdvertisingApiController::class, 'destroy']);
        Route::post('/search', [PostAdvertisingApiController::class, 'search']);
    });

    Route::group(['prefix' => 'visitor_details'], function(){
        Route::group(['middleware' => ['auth:users']], function(){
            Route::post('/setViewClick', [VisitorDetailApiController::class, 'setViewClick']);
        });
    });


    // Check status
    Route::get('/', [CheckStatusApiController::class, 'getStatus']);
});
