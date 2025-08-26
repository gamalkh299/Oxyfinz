<?php

namespace app\controllers\api;

use Yii;
use app\models\User;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\VerbFilter;

/**
 * API Auth Controller
 */
class AuthController extends Controller
{
    public $enableCsrfValidation = false;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
        ]
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login', 'register', 'logout','options'],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'login' => ['POST'],
                'register' => ['POST'],
                'logout' => ['POST'],
                'profile' => ['GET'],
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return true;
        }
        return false;
    }

    /**
     * User login
     * @return array
     */
    public function actionLogin()
    {

        $request = Yii::$app->request->post();
        
        if (empty($request['email']) || empty($request['password'])) {
            return [
                'status' => 'error',
                'message' => 'Username and password are required.',
            ];
        }

        $user = User::findByEmail($request['email']) ?: User::findByEmail($request['username']);

        if (!$user || !$user->validatePassword($request['password'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid username or password.',
            ];
        }

        // Generate new access token
        $user->generateAccessToken();
        $user->save(false);

        return [
            'status' => 'success',
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $user->access_token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->getFullName(),
                    'role' => $user->role,
                ],
            ],
        ];
    }

    /**
     * User registration
     * @return array
     */
    public function actionRegister()
    {
        $user = new User();
        $user->role = User::ROLE_USER;
        $user->status = User::STATUS_ACTIVE;
        
        $request = Yii::$app->request->post();
        
        if ($user->load($request, '') && $user->validate()) {
            if ($user->save()) {
                return [
                    'status' => 'success',
                    'message' => 'Registration successful.',
                    'data' => [
                        'access_token' => $user->access_token,
                        'user' => [
                            'id' => $user->id,
                            'username' => $user->username,
                            'email' => $user->email,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'full_name' => $user->getFullName(),
                            'role' => $user->role,
                        ],
                    ],
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Registration failed.',
            'errors' => $user->errors,
        ];
    }

    /**
     * User logout
     * @return array
     */
    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        if ($user) {
            $user->generateAccessToken(); // Generate new token to invalidate current one
            $user->save(false);
        }

        return [
            'status' => 'success',
            'message' => 'Logout successful.',
        ];
    }

    /**
     * Get user profile
     * @return array
     */
    public function actionProfile()
    {
        $user = Yii::$app->user->identity;
        
        return [
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->getFullName(),
                    'role' => $user->role,
                    'created_at' => date('Y-m-d H:i:s', $user->created_at),
                    'updated_at' => date('Y-m-d H:i:s', $user->updated_at),
                ],
            ],
        ];
    }
}