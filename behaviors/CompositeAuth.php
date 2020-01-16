<?php
namespace bricksasp\helpers\behaviors;

use bricksasp\rbac\components\Helper;
use bricksasp\rbac\models\redis\Token;
use bricksasp\helpers\Tools;
use Yii;

class CompositeAuth extends \yii\filters\auth\AuthMethod {
	public $saas_on = false;
	public $saas_owner_id = null;
	public $tokenParam = 'access-token';
	public $tokenHeader = 'X-Token';
	public $rbac_on = true;
	public $exemption = []; //免检

	public function authenticate($user, $request, $response) {
		$accessToken = $request->get($this->tokenParam);
		$authHeader = $request->getHeaders()->get($this->tokenHeader);

		if ($this->saas_on) {
			//开启平台功能 设置数据所属
			$action = Yii::$app->controller->action;
			if (!in_array($action->id, $this->exemption)) {
				$this->setOwnerId($user, $accessToken);
			}
		}

		// 未开启平台功能 默认数据所属
		if ($this->owner->ownerId === null) {
			$this->owner->ownerId = $this->saas_owner_id ?? 1;
		}

		// 设置uid
		return $this->setUid($user, $authHeader);
	}

	public function setUid($user, $token) {
		$action = Yii::$app->controller->action;
		$identity = null;
		// 传token则设置uid
		if ($token) {
			$identity = $user->loginByAccessToken($token);
			if ($identity === null) {
				Tools::exceptionBreak(50001);
			}
		}

		if ($identity !== null) {
			$this->owner->uid = $identity->id;

			if ($identity->token_type == Token::TOKEN_TYPE_FRONTEND) {
				$this->owner->request_identity = Token::IDENTITY_CURD;
				$this->owner->request_entrance = Token::TOKEN_TYPE_FRONTEND;
			}elseif ($identity->token_type == Token::TOKEN_TYPE_BACKEND) {
				$this->owner->request_identity = Token::IDENTITY_CURD;
				$this->owner->request_entrance = Token::TOKEN_TYPE_BACKEND;
			}/*elseif ($identity->token_type == Token::TOKEN_TYPE_BACKEND) {
				//授权用户 后期拓展
				$this->owner->request_identity = Token::IDENTITY_AUTHORIZE;
			}*/
		}

		// 免登录访问
		if ($this->owner->hasMethod('allowNoLoginAction') && in_array($action->id, $this->owner->allowNoLoginAction())) {
			return true;
		}

		if ($identity == null) {
			return Tools::exceptionBreak(50001);
		}

		//登录访问
		if ($this->owner->hasMethod('allowAction') && in_array($action->id, $this->owner->allowAction())) {
			return true;
		}

		if ($this->rbac_on) {
			// 授权访问
			if (Helper::checkRoute('/' . $action->getUniqueId(), Yii::$app->getRequest()->get(), $user->setIdentity($identity))) {
				return true;
			}
			Tools::exceptionBreak(50004);
		}
		Tools::exceptionBreak(50005);
	}

	public function setOwnerId($user, $token) {
		if ($this->saas_owner_id && $token == null) {
			$this->owner->ownerId = $this->saas_owner_id;
		} else {
			$identity = $user->loginByAccessToken($token, Token::TOKEN_TYPE_ACCESS);
			if ($identity == null) {
				Tools::exceptionBreak(50003);
			}

			$this->owner->ownerId = $identity->id;
		}
	}
}
