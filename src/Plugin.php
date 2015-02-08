<?php

namespace hashworks\Phergie\Plugin\GifToGfycat;

use Phergie\Irc\Bot\React\AbstractPlugin;
use \WyriHaximus\Phergie\Plugin\Http\Request;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Event\UserEvent as Event;

/**
 * Plugin class.
 *
 * @category Phergie
 * @package hashworks\Phergie\Plugin\GifToGfycat
 */
class Plugin extends AbstractPlugin {

	private $prefix = '[GIF to WEBM] ';
	private $limit = 10;

	public function __construct($config = array()) {
		if (isset($config['prefix'])) $this->prefix = $config['prefix'];
		if (isset($config['limit'])) $this->limit = intval($config['limit']);
	}

	/**
	 * @return array
	 */
	public function getSubscribedEvents () {
		return array(
				'irc.received.privmsg' => 'handleURL',
		);
	}

	/**
	 * Sends reply messages.
	 *
	 * @param Event        $event
	 * @param Queue        $queue
	 * @param array|string $messages
	 */
	protected function sendReply (Event $event, Queue $queue, $messages) {
		$method = 'irc' . $event->getCommand();
		if (is_array($messages)) {
			$target = $event->getSource();
			foreach ($messages as $message) {
				$queue->$method($target, $message);
			}
		} else {
			$queue->$method($event->getSource(), $messages);
		}
	}

	public function handleURL (Event $event, Queue $queue) {
		if (preg_match_all("/http[s]?:\/\/\S*\.gif\S*/i", $event->getMessage(), $matches)) {
			$matches = array_splice($matches[0], 0, $this->limit);
			$linkCount = count($matches);
			$webmLinks = array();

			$sendGeneratedLinks = function($linkCount, $webmLinks) use($event, $queue) {
					// Wait for all links being processed
				if (count($webmLinks) == $linkCount) {
					$string = join(' ', $webmLinks);
					if (!empty($string)) {
						$this->sendReply($event, $queue, $this->prefix . $string);
					}
				}
			};

			$errorHandler = function($linkCount, $webmLinks) use ($sendGeneratedLinks) {
				$linkCount--;
				$sendGeneratedLinks($linkCount, $webmLinks);
			};

			foreach ($matches as $link) {
				$this->emitter->emit('http.request', [new Request([
						'url'             => 'https://upload.gfycat.com/transcode?fetchUrl=' . rawurlencode($link),
						'resolveCallback' => function ($data) use ($event, $queue, &$linkCount, &$webmLinks, $sendGeneratedLinks, $errorHandler) {
							if (!empty($data) && ($data = json_decode($data, true)) !== NULL) {
								array_map('trim', $data);
								if (isset($data['gfyName']) && !empty($data['gfyName'])) {
									$webmLinks[] = 'https://gfycat.com/' . $data['gfyName'];
									$sendGeneratedLinks($linkCount, $webmLinks);
									return;
								}
							}
							// At this point no gfycat link was gathered, handle error
							$errorHandler($linkCount, $webmLinks);
						},
						'rejectCallback'  => $errorHandler($linkCount, $webmLinks)
				])]);
			}
		}
	}

}
