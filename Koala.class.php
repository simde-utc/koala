<?php namespace Koala;

use \Koala\ApiException;

$HTTP_HEADERS = array (
	100 => "Continue",
	101 => "Switching Protocols",
	200 => "OK",
	201 => "Created",
	202 => "Accepted",
	203 => "Non-Authoritative Information",
	204 => "No Content",
	205 => "Reset Content",
	206 => "Partial Content",
	300 => "Multiple Choices",
	301 => "Moved Permanently",
	302 => "Found",
	303 => "See Other",
	304 => "Not Modified",
	305 => "Use Proxy",
	307 => "Temporary Redirect",
	400 => "Bad Request",
	401 => "Non autorisé : une clé d'accès est nécessaire pour exécuter cette requête",
	402 => "Payment Required",
	403 => "Interdit : l'authentification est refusée",
	404 => "Non trouvé : la ressource demandée n'existe pas",
	405 => "Method Not Allowed",
	406 => "Not Acceptable",
	407 => "Proxy Authentication Required",
	408 => "Request Time-out",
	409 => "Conflict",
	410 => "Gone",
	411 => "Length Required",
	412 => "Precondition Failed",
	413 => "Request Entity Too Large",
	414 => "Request-URI Too Large",
	415 => "Unsupported Media Type",
	416 => "Requested range not satisfiable",
	417 => "Expectation Failed",
	500 => "Internal Server Error",
	501 => "Not Implemented",
	502 => "Bad Gateway",
	503 => "Service Unavailable",
	504 => "Gateway Time-out"      
);

  



class ApiException extends \Exception {
	protected $extendedMessage;
	public function __construct($code, $extendedMessage=NULL) {
		global $HTTP_HEADERS;
		parent::__construct($HTTP_HEADERS[$code], $code);
		$this->extendedMessage = $extendedMessage;
	}
	public function getExtendedMessage() {
		return $this->extendedMessage;
	}
}


class KoalaAuth {
	public function auth($app) {
		$key = $app->request()->params('key');
		if(empty($key))
			throw new ApiException(401);
	}
}


class Koala extends \Slim\Slim {
	
	public function __construct($auth, $arr) {
		parent::__construct($arr);
		$this->contentType('application/json; charset=utf-8');
		$self = $this;
		$this->error(function (\Exception $e) use ($self) { $self->onError($e); });
		$this->notFound(function () use ($self) { $self->on404(); });
		$this->hook('slim.before', function () use ($self) { $self->auth(); });
		$this->auth = $auth;
	}

	public function auth() {
		$this->auth->auth($this);
	}

	public function on404() {
		global $HTTP_HEADERS;
		$this->render('error.json.php', array('code'=>404, 'message'=>$HTTP_HEADERS[404]), 404);
	}

	public function onError(\Exception $e) {
		if (!($e instanceof ApiException)) {
			$code = 500;
		}
		else {
			$code = $e->getCode();
		}
		$message = $e->getMessage();
		$this->render('error.json.php', array('code'=>$code, 'message'=>$message), $code);
	}
}
