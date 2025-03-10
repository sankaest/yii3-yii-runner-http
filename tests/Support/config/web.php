<?php

declare(strict_types=1);

use HttpSoft\Message\Response;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use HttpSoft\Message\UriFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\ErrorHandler\Renderer\PlainTextRenderer;
use Yiisoft\ErrorHandler\ThrowableRendererInterface;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactory;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactoryInterface;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\Test\Support\Log\SimpleLogger;
use Yiisoft\Yii\Http\Application;
use Yiisoft\Yii\Http\Handler\NotFoundHandler;

return [
    EventDispatcherInterface::class => SimpleEventDispatcher::class,
    LoggerInterface::class => SimpleLogger::class,
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    ResponseFactoryInterface::class => ResponseFactory::class,
    ServerRequestFactoryInterface::class => ServerRequestFactory::class,
    StreamFactoryInterface::class => StreamFactory::class,
    ThrowableRendererInterface::class => PlainTextRenderer::class,
    UriFactoryInterface::class => UriFactory::class,
    UploadedFileFactoryInterface::class => UploadedFileFactory::class,

    ErrorCatcher::class => [
        'forceContentType()' => ['text/plain'],
    ],

    Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to(static function (ContainerInterface $container) {
                return $container
                    ->get(MiddlewareDispatcher::class)
                    ->withMiddlewares([
                        static fn () => new class () implements MiddlewareInterface {
                            public function process(
                                ServerRequestInterface $request,
                                RequestHandlerInterface $handler
                            ): ResponseInterface {
                                return (new Response())->withBody((new StreamFactory())->createStream('OK'));
                            }
                        },
                    ]);
            }),
            'fallbackHandler' => Reference::to(NotFoundHandler::class),
        ],
    ],
];
