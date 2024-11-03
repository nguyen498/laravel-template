<?php


namespace App\Services;

use App\Models\OtpAttempt;
use App\Models\User;
use App\Models\Version;
use App\Repositories\Interfaces\DeviceRepositoryInterface;
use App\Repositories\Interfaces\OtpAttemptRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\StringHelpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    protected $repo_base;
    protected $repo_otp_attempt;
    protected $repo_device;
//    protected $repo_version;
    protected $with;

    public function __construct(
        UserRepositoryInterface $repo_base,
        OtpAttemptRepositoryInterface $repo_otp_attempt,
        DeviceRepositoryInterface $repo_device
    ) {
        $this->repo_base = $repo_base;
        $this->repo_otp_attempt = $repo_otp_attempt;
        $this->repo_device = $repo_device;
        $this->with = ['devices'];
        $this->is_app = false;
    }

    public function getModelName()
    {
        return 'users';
    }

    public function getTableName()
    {
        return (new User())->getTable();
    }

    public function login($inputs)
    {
        if (!isset($inputs['phone'])) {
            return ['code' => '003', 'message' => 'Số điện thoại'];
        }
        $user = $this->repo_base->findOneBy([
            'phone' => $inputs['phone']
        ], [], ['*'], true);
        if (!isset($user)) {
            // generate otp to login with otp
            return $this->register($inputs);
        }
        if (isset($user->deleted_at) && !empty($user->deleted_at)) {
            return ['code' => '015', 'message' => ''];
        }
        if ($user->status === User::STATUS_UNACTIVE) {
            return ['code' => '102', 'message' => ''];
        }
        // create token for user base on room
        return [
            'code' => '200',
            'data' => [
                'is_otp' => false,
                'data' => [
                    'phone' => $user->phone
                ],
            ]
        ];
    }

    // TODO: should send to Sms before return to FE
    public function register($inputs)
    {
        if (isset($inputs['phone'])) {
            $data = $this->repo_otp_attempt->findOneBy([
                'phone' => $inputs['phone'],
                'type' => OtpAttempt::REGISTER,
                'is_confirm' => false,
                'status' => OtpAttempt::NOT_USE
            ]);
        }

        $isCreate = false;
        if (!isset($data)) {
            $isCreate = true;
        } else {
            $created_at = Carbon::parse($data->created_at);
            if ($created_at->diffInSeconds(Carbon::now()) >= $data->valid_in) {
                $data->update(['status' => OtpAttempt::CANCEL]);
                $isCreate = true;
            } // device already register, have to wait for next register account
            else if ($data->is_confirm) {
                return ['code' => '010', 'message' => ''];
            } else if ($data->attempts < config('constants.sms.limit_count')) {
                $otp = '999999';
                if (config('constants.dev_mode') !== 1) {
                    $otp = StringHelpers::generateRandomNumber(6);
                }
                $data->otp = $otp;
                $data->attempts += 1;
                $data->update();
            } else {
                return ['code' => '009', 'message' => ''];
            }
        }
        if ($isCreate) {
            $inputs['otp'] = '999999';
            if (config('constants.dev_mode') !== 1) {
                $inputs['otp'] = StringHelpers::generateRandomNumber(6);
            }

            $data = $this->repo_otp_attempt->create([
                'phone' => isset($inputs['phone']) ? $inputs['phone'] : null,
                'status' => OtpAttempt::NOT_USE,
                'otp' => $inputs['otp'],
                'valid_in' => (config('constants.sms.valid_in') * 60),
                'attempts' => 1,
                'type' => OtpAttempt::REGISTER,
                'device_id' => isset($inputs['device_id']) ? $inputs['device_id'] : null
            ]);
        }

        return [
            'code' => '200',
            'data' => [
                'is_otp' => true,
                'data' => [
                    'id' => $data->id,
                    'phone' => $data->phone,
                ]
            ]
        ];
    }

    public function loginWithPassword($inputs)
    {
        $this->is_app = true;
        if (!isset($inputs['phone'])) {
            return ['code' => '003', 'message' => 'Số điện thoại'];
        }
        if (!isset($inputs['password'])) {
            return ['code' => '003', 'message' => 'mật khẩu'];
        }
        $input_users = ['last_login_at' => Carbon::now()->toDateTimeString()];

        $user = $this->repo_base->findOneBy([
            'phone' => $inputs['phone'],
            'status' => User::STATUS_ACTIVE,
        ]);
        if (!isset($user)) {
            return ['code' => '101', 'message' => ''];
        }
        if (in_array($user->status, [User::STATUS_UNACTIVE])) {
            return ['code' => '102', 'message' => ''];
        }

        if (!Hash::check($inputs['password'], $user->password)) {
            return ['code' => '016', 'message' => ''];
        }

        if (isset($inputs['push_token']) && $inputs['push_token'] != null) {
            $device_info = isset($inputs['device_info']) ? $inputs['device_info'] : null;
            $this->repo_device->loginBy($inputs['push_token'], $device_info, $user->id, $this->getTableName());
        }
        if (isset($inputs['name']) && !empty($inputs['name'])) {
            $input_users['name'] = $inputs['name'];
        }
        $user->update($input_users);
        $user = $this->repo_base->findById($user->id, $this->with);

        $token = $user->createToken(config('constants.default_app'), ['users'])->accessToken;
        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatData($user),
                'token' => $token
            ]
        ];
    }

    public function loginWithToken($inputs)
    {
        $this->is_app = true;
        $user = Auth::guard('users')->user();

        if(!isset($user)){
            return [
                'code' => '401',
                'message' => ''
            ];
        }
        if (in_array($user->status, [User::STATUS_UNACTIVE])) {
            return ['code' => '101', 'message' => ''];
        }

        // TODO: send to notification services
        if (isset($inputs['push_token']) && $inputs['push_token'] != null) {
            $device_info = isset($inputs['device_info']) ? $inputs['device_info'] : null;
            $this->repo_device->loginBy($inputs['push_token'], $device_info, $user->id, $this->getTableName());
        }

        $user = $this->repo_base->findById($user->id, $this->with);
        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatData($user)
            ]
        ];
    }

    public function verifyOtp($inputs)
    {
        $data_attempt = $this->repo_otp_attempt->findById(['id' => $inputs['id']]);
        if (!isset($data_attempt) || (isset($data_attempt) && $data_attempt->is_confirm != 0)) {
            return [
                'code' => '008',
                'message' => 'Mã OTP'
            ];
        }
        // remember recall if expire
        if ($inputs['otp'] == $data_attempt->otp) {
            $this->repo_otp_attempt->update($inputs['id'], [
                'is_confirm' => true,
                'status' => OtpAttempt::USE
            ]);

            return [
                'code' => '200',
                'data' => [
                    'is_otp' => true,
                    'is_create_password' => true,
                    'data' => $data_attempt->id
                ]
            ];
        }
        return ['code' => '008', 'message' => 'Mã OTP'];
    }

    public function registerWithPassword($inputs)
    {
        $this->is_app = true;
        if (!isset($inputs['id'])) {
            return ['code' => '003', 'message' => 'Id otp attempt'];
        }
        if (!isset($inputs['password'])) {
            return ['code' => '003', 'message' => 'Mật khẩu'];
        }

        $data_attempt = $this->repo_otp_attempt->findById(['id' => $inputs['id']]);
        if ($data_attempt->status !== OtpAttempt::USE) {
            return ['code' => '008', 'message' => 'Dữ liệu OTP '];
        }
        $this->repo_otp_attempt->update($data_attempt->id, ['status' => OtpAttempt::DONE]);

        $data = $this->repo_base->create([
//            'name' => isset($inputs['name']) ? $inputs['name'] : null,
            'password' => bcrypt($inputs['password']),
            'phone' => $data_attempt->phone,
            'reference' => $this->generateReference(null),
            'type' => User::TYPE_PHONE,
            'status' => User::STATUS_ACTIVE
        ]);

        // create token for user base on room
        $token = $data->createToken(config('constants.default_app'), ['users'])->accessToken;
        $data = $this->repo_base->findById($data->id);

        return [
            'code' => '200',
            'data' => [
                'data' => $this->formatData($data),
                'token' => $token
            ]
        ];
    }
    // TODO: should send to Sms before return to FE
    public function forgetPassword($inputs)
    {
        if (!isset($inputs['phone'])) {
            return ['code' => '003', 'message' => 'Số điện thoại'];
        }

        $user = $this->repo_base->findOneBy(['phone' => $inputs['phone']], [], ['*'], true);
        if (!isset($user) || (isset($user->deleted_at) && !empty($user->deleted_at)) ) {
            return ['code' => '008', 'message' => 'Số điện thoại'];
        }

        $data = $this->repo_otp_attempt->findOneBy([
            'phone' => $inputs['phone'],
            'is_confirm' => false,
            'type' => OtpAttempt::FORGET_PASSWORD,
            'status' => OtpAttempt::NOT_USE
        ]);
        $isCreate = false;
        if (!isset($data)) {
            $isCreate = true;
        } else {
            $created_at = Carbon::parse($data->created_at);
            if ($created_at->diffInSeconds(Carbon::now()) >= $data->valid_in) {
                $data->update(['status' => OtpAttempt::CANCEL]);
                $isCreate = true;
            } else if ($data->is_confirm) {
                return ['code' => '011', 'message' => ''];
            } else if ($data->attempts < config('constants.sms.limit_count')) {
                $otp = '999999';
                if (config('constants.dev_mode') !== 1) {
                    $otp = StringHelpers::generateRandomNumber(6);
                }
                $data->otp = $otp;
                $data->attempts += 1;
                $data->update();
            } else {
                return ['code' => '012', 'message' => ''];
            }
        }
        if ($isCreate) {
            $otp = '999999';
            if (config('constants.dev_mode') !== 1) {
                $otp = StringHelpers::generateRandomNumber(6);
            }

            $data = $this->repo_otp_attempt->create([
                'phone' => $user->phone,
                'status' => OtpAttempt::NOT_USE,
                'is_confirm' => false,
                'otp' => $otp,
                'valid_in' => (config('constants.sms.valid_in') * 60),
                'attempts' => 1,
                'type' => OtpAttempt::FORGET_PASSWORD,
                'device_id' => isset($inputs['device_id']) ? $inputs['device_id'] : null
            ]);
        }

        return [
            'code' => '200',
            'data' => [
                'is_otp' => true,
                'is_reset_password' => true,
                'data' => [
                    'id' => $data->id,
                    'phone' => $data->phone,
                ]
            ]
        ];
    }

    public function verifyResetPassword($inputs)
    {
        if (!isset($inputs['id'])) {
            return ['code' => '003', 'message' => 'Id Otp'];
        }
        if (!isset($inputs['otp'])) {
            return ['code' => '003', 'message' => 'Mã OTP'];
        }

        $this->is_app = true;
        $data = $this->repo_otp_attempt->findOneBy([
            'id' => $inputs['id'],
            'is_confirm' => false,
            'type' => OtpAttempt::FORGET_PASSWORD,
            'status' => OtpAttempt::NOT_USE
        ]);
        if (!isset($data) || (isset($data) && $data->is_confirm != 0)) {
            return [
                'code' => '008',
                'message' => 'Mã OTP'
            ];
        }
        $created_at = Carbon::parse($data->created_at);
        if (!isset($data)) {
            return [
                'code'  => '004',
                'message' => 'Mã OTP'
            ];
        } else if ($created_at->diffInSeconds(Carbon::now()) > $data->valid_in) {
            $data->update(['status' => OtpAttempt::CANCEL]);
            return [
                'code' => '013',
                'message' => 'Reset password'
            ];
        }
        if ($inputs['otp'] == $data->otp) {
            $data->update([
                'is_confirm' => true,
                'status' => OtpAttempt::USE
            ]);
            $user = $this->repo_base->findOneBy(['phone' => $data->phone]);

            if (!isset($user)) {
                return ['code' => '004', 'message' => 'Tài khoản '];
            }
            $token = $user->createToken(config('constants.default_app'), ['users'])->accessToken;

            return [
                'code' => '200',
                'data' => [
                    'is_otp' => false,
                    'data' => $user,
                    'token' => $token
                ]
            ];
        }
        return [
            'code' => '008',
            'message' => 'Mã OTP'
        ];
    }

    public function changePassword($inputs)
    {
        $this->is_app = true;
        $user = Auth::guard('users')->user();
        if (!isset($inputs['password'])) {
            return ['code' => '003', 'message' => 'Mật khẩu'];
        }

        if (in_array($user->status, [User::STATUS_UNACTIVE])) {
            return ['code' => '101', 'message' => ''];
        }

        if (isset($inputs['is_change_password']) && $inputs['is_change_password'] == 1) {
            if (!isset($inputs['old_password'])) {
                return ['code' => '003', 'message' => 'Mật khẩu cũ'];
            }
            if (!Hash::check($inputs['old_password'], $user->password)) {
                return ['code' => '016', 'message' => ''];
            }
        }
        $inputs['password'] = Hash::make($inputs['password']);
        $input_update['password'] = $inputs['password'];

        $user = $this->repo_base->update($user->id, $input_update);
        $user = $this->repo_base->findById($user->id, $this->with);

        return [
            'code' => '200',
            'data' => $this->formatData($user)
        ];
    }

    public function deleteByPassword($inputs)
    {
        $this->is_app = true;
        $user = Auth::guard('users')->user();
        if (!isset($inputs['password'])) {
            return ['code' => '003', 'message' => 'Mật khẩu'];
        }

        if (!Hash::check($inputs['password'], $user->password)) {
            return ['code' => '016', 'message' => ''];
        }
        // delete all token
        foreach($user->tokens as $token) {
            $token->delete();
        }
        // remove all devices
        $user->devices()->delete();
        // should delete all device
        $user->delete();

        return [
            'code' => '200',
            'message' => 'Deleted successfully'
        ];
    }
    // TODO: should send to sms
    public function resendOtp($inputs)
    {
        $data = $this->repo_otp_attempt->findOneBy([
            'id' => $inputs['id'],
            'is_confirm' => false,
            'status' => OtpAttempt::NOT_USE
        ]);
        if (!isset($data)) {
            return ['code' => '008', 'message' => 'Dữ liệu'];
        }
        $type = $data->type;

        $isCreate = false;
        $created_at = Carbon::parse($data->created_at);
        if ($created_at->diffInSeconds(Carbon::now()) > $data->valid_in) {
            $data->update(['status' => OtpAttempt::CANCEL]);
            $isCreate = true;
        } else if ($data->is_confirm) {
            if ($type === OtpAttempt::FORGET_PASSWORD) {
                return ['code' => '011', 'message' => ''];
            }
            return ['code' => '010', 'message' => ''];
        } else if ($data->attempts < config('constants.sms.limit_count')) {
            $otp = '999999';
            if (config('constants.dev_mode') !== 1) {
                $otp = StringHelpers::generateRandomNumber(6);
            }
            $data->otp = $otp;
            $data->attempts += 1;
            $data->update();
        } else {
            if ($type === OtpAttempt::FORGET_PASSWORD) {
                return ['code' => '012', 'message' => ''];
            }
            return ['code' => '009', 'message' => ''];
        }
        if ($isCreate) {
            $otp = '999999';
            if (config('constants.dev_mode') !== 1) {
                $otp = StringHelpers::generateRandomNumber(6);
            }

            $data = $this->repo_otp_attempt->create([
                'phone' => $data->phone,
                'status' => OtpAttempt::NOT_USE,
                'is_confirm' => false,
                'otp' => $otp,
                'valid_in' => (config('constants.sms.valid_in') * 60),
                'attempts' => 1,
                'type' => $type,
                'device_id' => isset($data->device_id) ? $data->device_id : null
            ]);
        }

        return [
            'code' => '200',
            'data' => [
                'is_otp' => true,
                'data' => [
                    'id' => $data->id,
                    'phone' => $data->phone,
                ]
            ]
        ];
    }

    public function logout($inputs)
    {
        $user = Auth::user();
        // TODO: send to notification services
        if (isset($inputs['push_token'])) {
            $this->repo_device->logoutBy($inputs['push_token'], $user->id, $this->getTableName());
        }
        // revoke 1 token
        $user->token()->revoke();
        // revoke all token
        foreach($user->tokens as $token) {
            $token->revoke();
        }
        // remove all devices
        $user->devices()->delete();

        $messages = 'Đăng xuất thành công';
        return [
            'code' => '200',
            'message' => $messages
        ];
    }

    public function updateProfile($inputs)
    {
        $this->is_app = true;
        $passenger = Auth::user();
        if (in_array($passenger->status, [User::STATUS_UNACTIVE])) {
            return ['code' => '101', 'message' => ''];
        }
        return $this->update($passenger->id, $inputs);
    }

    // TODO: reopen when have version repo
    public function checkVersion($inputs)
    {
        $is_failed = false;
        if (!isset($inputs['version'])) {
            return ['code' => '003', 'message' => 'version'];
        }
//        $version = $this->getVersionApp();
//        if (isset($inputs['version'])) {
//            $compareVersion = explode('.', $inputs['version']);
//            $currentVersion = explode('.', $version->value);
//            // compare 1
//            $is_compare = 1;
//            foreach ($compareVersion as $key => $val) {
//                if ($is_compare == 1 && isset($currentVersion[$key])) {
//                    $is_compare = $this->compareVersion($val, $currentVersion[$key]);
//                } else {
//                    break;
//                }
//            }
//
//            // only set to true when is_compare == 0;
//            if ($is_compare == 0) {
//                $is_failed = true;
//            }
//        }
//
//
//        if ($is_failed) {
//            return [
//                'code' => '014',
//                'message' => config('error_code.014'),
//                'is_failed' => $is_failed,
//                'data' => [
//                    'required' => $version->force_update,
//                    'notes' => $version->notes,
//                    'link' => [
//                        'android' => $version->android,
//                        'ios' => $version->ios
//                    ]
//                ]
//            ];
//        }

        return [
            'code' => '200',
            'message' => 'Version hợp lệ'
        ];
    }

//    private function compareVersion($compare, $current)
//    {
//        if ($compare > $current) {
//            return 2;
//        } else if ($compare == $current) {
//            return 1;
//        }
//        return 0;
//    }

//    private function getVersionApp()
//    {
//        $version = $this->repo_version->findOneBy([
//            'type' => Version::APP_VERSION
//        ]);
//        if (!isset($version)) {
//            $version = $this->repo_version->create([
//                'type' => Version::APP_VERSION,
//                'status' => Version::ACTIVE,
//                'value' => config('constants.version.current'),
//                'force_update' => false,
//                'android' => config('constants.version.google_url'),
//                'ios' => config('constants.version.apple_url'),
//            ]);
//        }
//        return $version;
//    }

    // TODO: should send to Sms
    public function sendSms($inputs)
    {
        $data_attempt = $this->repo_otp_attempt->findById(['id' => $inputs['id']]);
        if (!isset($data_attempt) || (isset($data_attempt) && $data_attempt->is_confirm != 0)) {
            return [
                'code' => '008',
                'message' => 'Mã OTP ' . config('error_code.008')
            ];
        }
//        return $this->service_sms->sendSms([
//            'otp' => $data_attempt->otp,
//            'phone' => $data_attempt->phone
//        ]);
    }

    public function generateReference($reference)
    {
        if (!isset($reference)) {
            return $this->repo_base->getReference();
        }
        return $reference;
    }

    public function getJoinTable()
    {
        return [];
    }

    public function getQueryDateField()
    {
        return [
            $this->getTableName() . '.created_at',
            $this->getTableName() . '.updated_at',
        ];
    }

    public function getQueryField()
    {
        return [
            $this->getTableName() . '.name',
            $this->getTableName() . '.email',
        ];
    }

    public function generateColumn($inputs, $columns)
    {
        if (isset($inputs['first_name']) && $inputs['first_name'] !== 'all') {
            array_push($columns, $this->getTableName() . ".first_name = '" . $inputs['first_name'] . "'");
        }
        if (isset($inputs['last_name']) && $inputs['last_name'] !== 'all') {
            array_push($columns, $this->getTableName() . ".last_name = '" . $inputs['last_name'] . "'");
        }
        if (isset($inputs['email']) && $inputs['email'] !== 'all') {
            array_push($columns, $this->getTableName() . ".email = '" . $inputs['email'] . "'");
        }
        return $columns;
    }
}
