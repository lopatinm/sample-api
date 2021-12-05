<?php

namespace app\modules\v1\models;
use http\Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\HttpException;
/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $phone
 * @property string $password
 * @property string $token
 * @property string $fullname
 * @property string $email
 * @property string $photo
 *
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone', 'password', 'fullname'], 'required'],
            [['phone', 'photo', 'password', 'token', 'email', 'fullname'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'password' => 'Password',
            'fullname' => 'Fullname',
            'email' => 'Email',
            'photo' => 'Photo',
            'token' => 'Token',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password'], $fields['token']);
        return $fields;
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    public static function issetUser($phone)
    {
        $userObject = User::find()->where(array('phone' => $phone))->orderBy(['id' => SORT_DESC])->asArray()->One();
        if ($userObject && count($userObject) != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $id
     * @param $request
     * @return array
     * @throws HttpException
     */
    public static function updateUser($id, $request)
    {
        $response = array();

        $phone = isset($request['phone']) ? static::trimPhone($request['phone']) : "";
        $password = isset($request['password']) ? $request['password'] : "";
        $email = isset($request['email']) ? $request['email'] : "";
        $photo = isset($request['photo']) ? $request['photo'] : "";
        $fullname = isset($request['fullname']) ? $request['fullname'] : "";


        $user = static::findOne(['id' => $id]);
        if($user->phone != $phone && static::issetUser(static::trimPhone($phone))) {
            throw new HttpException(409, sprintf('A user with this phone number exists.'));
        }

        if ($user) {
            if($phone != "") {
                $user->phone = $phone;
            }
            if($fullname != "") {
                $user->fullname = $fullname;
            }
            if($photo != "") {
                $user->photo = $photo;
            }
            if($email != "") {
                $user->email = $email;
            }
            if($password != "") {
                $user->password = password_hash($password, PASSWORD_DEFAULT);
            }
            $user->save();

            $response = [
                'name' => 'Update',
                'message' => 'Data updated successfully',
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'photo' => $user->photo,
                ],
            ];
        }
        return $response;
    }

    /**
     * @param $request
     * @return array
     * @throws HttpException
     */
    public static function loginUser($request)
    {
        $response = array();
        try {
            $phone = static::trimPhone($request['phone']);
            $password = $request['password'];
            $user = static::findByUsername($phone);
            if ($user && password_verify($password, $user->password)) {

                $response = [
                    'name' => 'Login',
                    'message' => 'User is successfully authorized',
                    'code' => 200,
                    'status' => 'success',
                    'data' => [
                        'id' => $user->id,
                        'phone' => $user->phone,
                        'fullname' => $user->fullname,
                        'email' => $user->email,
                        'photo' => $user->photo,
                        'role' => static::getRole(Yii::$app->authManager->getRolesByUser($user->id)),
                        'token' => $user->token,
                    ],
                ];

            } else {
                throw new HttpException(401, sprintf('Login or password is incorrect.'));
            }
        } catch (InvalidConfigException $e) {
        }

        return $response;
    }


    /**
     * @param $request
     * @return array
     * @throws HttpException
     * @throws \Exception
     * @throws \yii\base\Exception
     */
    public static function registrationUser($request)
    {
        $password = static::generatePassword(8);
        $phone = static::trimPhone($request['phone']);
        if(mb_strlen($phone) != 11){
            throw new HttpException(400, sprintf('Invalid value specified.'));
        }
        $fullname = $request['fullname'];
        if(!static::issetUser(static::trimPhone($phone))) {
            $user = new User;
            $user->phone = $phone;
            $user->fullname = $fullname;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $user->token = Yii::$app->security->generateRandomString(64);
            } catch (Exception $e) {
                $response = [
                    'status' => 'error',
                    'message' => $e,
                ];
            }
            $user->photo = $phone;
            $user->email = $phone."@sample.ru";
            $user->save();
            $auth = Yii::$app->authManager;
            $authorRole = $auth->getRole('user');
            $auth->assign($authorRole, $user->id);
            $response = [
                'name' => 'Registration',
                'message' => 'User '.$fullname.' registered successfully.',
                'code' => 200,
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'password' => $password,
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'photo' => $user->photo,
                    'role' => static::getRole(Yii::$app->authManager->getRolesByUser($user->id)),
                    'token' => $user->token,
                ],
            ];
        }else{
            throw new HttpException(409, sprintf('A user with this phone number exists.'));
        }

        return $response;
    }

    public static function findByUsername($phone)
    {
        return static::findOne(['phone' => $phone]);
    }

    public static function generatePassword($n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    public static function trimPhone($phone)
    {
        return preg_replace('~[^0-9]+~','', $phone);
    }

    public static function getRole($roles)
    {
        $userRole = '';
        foreach ($roles as $role => $val){
            $userRole = $role;
        }
        return $userRole;
    }
}
