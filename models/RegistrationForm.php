<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dektrium\user\models;

use Yii;
use yii\base\Model;
use dektrium\user\traits\ModuleTrait;
use dektrium\user\helpers\StrengthValidator;

/**
 * Registration form collects user input on registration process, validates it and creates new User model.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RegistrationForm extends Model
{
    use ModuleTrait;
    /**
     * @var string User email address
     */
    public $email;

    /**
     * @var string Username
     */
    public $username;

    /**
     * @var string Password
     */
    public $password;

    /**
     * @var string Password repetition
     */
    public $passwordRepeat;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $user = $this->module->modelMap['User'];

        $rules = [
            // email rules
            'emailTrim'     => ['email', 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $user,
                'message' => Yii::t('user', 'This email address has already been taken')
            ],
            // password rules
            'passwordRequired' => [['password', 'passwordRepeat'], 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            'passwordCompare' => ['passwordRepeat',
                'compare',
                'compareAttribute'=>'password',
                'skipOnEmpty' => $this->module->enableGeneratingPassword,
                'message'=> Yii::t('user', 'Passwords are not the same')
            ],

        ];

        if ($this->module->enableStrengthValidaton === true) {
            $rules_password = [
                'passwordStrength' => array_merge(
                    ['password', StrengthValidator::className()],
                $this->module->passwordStrengthConfig
                ),
            ];
        } else {
            $rules_password = [
                'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72],
            ];
        }

        if ($this->module->requireUsername === true) {
            // username rules
            $rules_user = [
                'usernameTrim'     => ['username', 'trim'],
                'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
                'usernamePattern'  => ['username', 'match', 'pattern' => $user::$usernameRegexp],
                'usernameRequired' => ['username', 'required'],
                'usernameUnique'   => [
                    'username',
                    'unique',
                    'targetClass' => $user,
                    'message' => Yii::t('user', 'This username has already been taken')
                ]
            ];

            $rules = array_merge($rules, $rules_password, $rules_user);
        } else {
            $rules = array_merge($rules, $rules_password);
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
            'passwordRepeat' => Yii::t('user', 'Password repeat'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * Registers a new user account. If registration was successful it will set flash message.
     *
     * @return bool
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var User $user */
        $user = Yii::createObject(User::className());
        $user->setScenario('register');
        $this->loadAttributes($user);

        if (!$user->register()) {
            return false;
        }

        Yii::$app->session->setFlash(
            'info',
            Yii::t(
                'user',
                'Your account has been created and a message with further instructions has been sent to your email'
            )
        );

        return true;
    }

    /**
     * Loads attributes to the user model. You should override this method if you are going to add new fields to the
     * registration form. You can read more in special guide.
     *
     * By default this method set all attributes of this model to the attributes of User model, so you should properly
     * configure safe attributes of your User model.
     *
     * @param User $user
     */
    protected function loadAttributes(User $user)
    {
        $user->setAttributes($this->attributes);
    }
}
