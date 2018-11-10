<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
		
		
		if (method_exists($exception, 'render') && $response = $exception->render($request)) {
			return \Illuminate\Routing\Router::toResponse($request, $response);
		} elseif ($exception instanceof \Illuminate\Contracts\Support\Responsable) {
			return $exception->toResponse($request);
		}

		$exception = $this->prepareException($exception);

	//	if ($exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
	//		return $exception->getResponse();
	//	} else
		if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
			return $this->unauthenticated($request, $exception);
		} elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
			return $this->convertValidationExceptionToResponse($exception, $request);
		}
		

	//	return $request->expectsJson()
	//					? $this->prepareJsonResponse($request, $exception)
	//					: $this->prepareResponse($request, $exception);
		
		
		$code=method_exists($exception,'getStatusCode')?$exception->getStatusCode():500;
		$message=$this->statusTexts[$code];
		
		\Illuminate\Support\Facades\DB::table('log_error')->insert([
			'code'=>$code,
	    	'method'=>$request->method(),
			'message'=>$message,
			'description'=>(method_exists($exception,'getMessage')?$exception->getMessage():'').(method_exists($exception,'getFile')?'

'.$exception->getFile().(method_exists($exception,'getLine')?' L'.$exception->getLine():''):''),
			'location'=>\Illuminate\Support\Facades\URL::current(),
			'user_agent'=>$request->server('HTTP_USER_AGENT'),
			'ip_address'=>$request->ip(),
		]);
		
		// prepareResponse()에서
		
		if(!$this->isHttpException($exception)){
			/*
			if($request->ip()=='14.33.153.60'){
				
				// convertExceptionToResponse()에서
				$headers = $this->isHttpException($exception) ? $exception->getHeaders() : [];
		
				$statusCode = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;
		
				try {
					$content = class_exists(\Whoops\Run::class)
							? $this->renderExceptionWithWhoops($exception)
							: $this->renderExceptionWithSymfony($exception, true);
				} catch (Exception $exception) {
					$content = $content ?? $this->renderExceptionWithSymfony($exception, true);
				}
		
				$convertExceptionToResponse=\Symfony\Component\HttpFoundation\Response::create(
					$content, $statusCode, $headers
				);
				
				return $this->toIlluminateResponse(
				//	$this->convertExceptionToResponse($exception), $exception
					$convertExceptionToResponse, $exception
				);
			}
			*/
			
			$exception=new \Symfony\Component\HttpKernel\Exception\HttpException(500,$exception->getMessage());
		}
		
		return response()->view('error',['message'=>$message,'exception'=>$exception],$code,$exception->getHeaders());
		
    //    return parent::render($request, $exception);
    }
	
	public $statusTexts = array(
		100 => '계속해서 기다리고 있습니다.',
		101 => '프로토콜 전환을 승인 중입니다.',
		102 => '처리 중입니다.',
		103 => '힌트입니다.',
		200 => '성공!',
		201 => '만들어졌습니다.',
		202 => '허용되었습니다.',
		203 => '신뢰할 수 없는 정보입니다.',
		204 => '내용이 없습니다.',
		205 => '내용을 재설정합니다.',
		206 => '일부 내용입니다.',
		207 => '여러 개의 상태가 중첩되어있습니다.',
		208 => '이미 보고되었습니다.',
		226 => 'IM 사용되었습니다.',
		300 => '여러 개 중에 선택하십시오.',
		301 => '영구적으로 이동된 항목입니다.',
		302 => '임시적으로 이동되었고, 찾았습니다.',
		303 => '다른 곳을 보세요.',
		304 => '마지막으로 불러온 이후로 수정되지 않았습니다.',
		305 => '프록시를 사용하세요.',
		307 => '잠깐만 임시로 이쪽으로 안내하겠습니다. 다음에도 이쪽으로 요청하세요.',
		308 => '앞으로 이곳으로 안내하겠습니다. 다음에는 안내한 곳으로 요청하세요.',
		400 => '영 좋지 못한 요청입니다.',
		401 => '권한이 없습니다.',
		402 => '결제는 하셨나요?',
		403 => '당신은 금지되었습니다.',
		404 => '찾을 수 없습니다.',
		405 => '허용되지 않는 방법입니다.',
		406 => '당신은 허용되지 않았습니다.',
		407 => '프록시 인증이 필요합니다.',
		408 => '오래 기다리셨습니다만, 저도 서버를 기다리다 지쳐버렸습니다.',
		409 => '으악! 충돌 발생!',
		410 => '사라진 항목입니다...',
		411 => '당신의 길이를 알 수 없어서 보여드릴 수 없습니다.',
		412 => '당신은 우리가 예상했던 것과 다른 조건을 가지고 있습니다.',
		413 => '너무 큰 걸 가져다 주셨네요.',
		414 => '주소가 너무 길어서 앞 부분을 까먹었어요.',
		415 => '이런 종류는 우리가 처리할 수 없습니다.',
		416 => '이런 범위는 우리가 처리할 수 없습니다.',
		417 => '요구사항을 만족하지 않고 있습니다.',
		418 => '저는 찻주전자의 요청은 받지 않습니다.',
		419 => '알 수 없는 상태입니다.',
		421 => '이쪽이 아닌 것 같습니다.',
		422 => '처리할 수 없는 엔티티를 가지고 오셨습니다.',
		423 => '잠겼습니다.',
		424 => '의존할 게 필요한데 실패했습니다.',
		425 => 'WebDAV 고급 콜렉션의 만료된 프로포절을 가지고 오셨습니다.',
		426 => '업그레이드가 필요합니다.',
		428 => '전제조건이 필요합니다.',
		429 => '짧은 시간 동안 너무 많은 요청을 보냈습니다.',
		431 => '요청 헤더가 너무 큽니다.',
		451 => '법적 이유 때문에 사용할 수 없습니다.',
		500 => '서버의 문제인 것 같은데, 전산 담당자에게 알려주세요.',
		501 => '구현되지 않았습니다.',
		502 => '영 좋지 못한 게이트웨이입니다.',
		503 => '사용할 수 없는 서비스입니다.',
		504 => '오래 기다리셨습니다만, 저도 게이트웨이를 기다리다 지쳐버렸습니다.',
		505 => '지원하지 않는 HTTP 버전입니다.',
		506 => '다른 곳에서도 협상을 하고 있습니다.',
		507 => '이 안쪽은 이미 가득 찼습니다.',
		508 => '왔다갔다 거리기만 하고 있습니다.',
		510 => '확장되지 않았는데요...',
		511 => '네트워크 인증이 필요합니다.',
	);
}
