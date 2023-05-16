<?php
declare(strict_types=1);

namespace App\Routes;

require_once APP_ROOT . '/src/Widgets/Box.php';

use App\Widgets\Box;
use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeType;
use Elephox\Templar\EdgeInsets;
use Elephox\Templar\Foundation\Column;
use Elephox\Templar\Foundation\Container;
use Elephox\Templar\Foundation\FullscreenBody;
use Elephox\Templar\Foundation\FullscreenDocument;
use Elephox\Templar\Foundation\Head;
use Elephox\Templar\Foundation\Hyperlink;
use Elephox\Templar\Foundation\Link;
use Elephox\Templar\Foundation\Text;
use Elephox\Templar\Foundation\Title;
use Elephox\Templar\Length;
use Elephox\Templar\Templar;
use Elephox\Templar\TextStyle;
use Elephox\Templar\Widget;
use Elephox\Web\Routing\Attribute\Controller;
use Elephox\Web\Routing\Attribute\Http\Any;
use Elephox\Web\Routing\Attribute\Http\Get;
use ErrorException;

#[Controller]
class WebController {
	/**
	 * @throws ErrorException
	 */
	#[Get]
	public function index(): ResponseBuilder {
		return Response::build()->ok()->htmlBody(
			(new Templar())->render($this->getContent())
		);
	}

	#[Get('style.css')]
	public function style(): ResponseBuilder {
		return Response::build()->ok()->htmlBody(
			(new Templar())->renderStyle($this->getContent()),
			MimeType::TextCss
		);
	}

	private function getContent(): Widget {
		return new FullscreenDocument(
			head: new Head(
				children: [
					new Title("Seq Logging"),
					new Link('/style.css'),
				],
			),
			body: new FullscreenBody(
				child: new Container(
					child: new Column(
						children: [
							new Container(
								child: new Text(
									"Seq Logging",
									style: new TextStyle(
										size: Length::inRem(3),
										weight: 600,
									),
								),
								margin: EdgeInsets::only(bottom: Length::inRem(1.5)),
							),
							new Box(
								new Text("This request was logged"),
								new Text("Take a look at your Seq events."),
							),
							new Box(
								new Text("Generate more logs"),
								new Hyperlink("Reload page", "/?random=" . uniqid()),
							),
						],
					),
					padding: EdgeInsets::symmetric(
						Length::inRem(1.5),
						Length::inRem(3),
					),
				),
			),
		);
	}
}
