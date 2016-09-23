<?php

namespace RMS\PushNotifications\Handlers;

use RMS\PushNotifications\Exception\InvalidMessageTypeException,
    RMS\PushNotifications\Message\AndroidMessage,
    RMS\PushNotifications\Message\MessageInterface;
use Buzz\Browser;

class AndroidNotificationHandler implements NotificationHandlerInterface
{
    /**
     * Username for auth
     *
     * @var string
     */
    protected $username;

    /**
     * Password for auth
     *
     * @var string
     */
    protected $password;

    /**
     * The source of the notification
     * eg com.example.myapp
     *
     * @var string
     */
    protected $source;

    /**
     * Timeout in seconds for the connecting client
     *
     * @var int
     */
    protected $timeout;

    /**
     * Authentication token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Constructor
     *
     * @param $username
     * @param $password
     * @param $source
     * @param $timeout
     */
    public function __construct($username, $password, $source, $timeout)
    {
        $this->username = $username;
        $this->password = $password;
        $this->source = $source;
        $this->timeout = $timeout;
        $this->authToken = "";
    }

    /**
     * Sends a C2DM message
     * This assumes that a valid auth token can be obtained
     *
     * @param  \RMS\PushNotifications\Message\MessageInterface              $message
     * @throws \RMS\PushNotifications\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof AndroidMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by C2DM", get_class($message)));
        }

        if ($this->getAuthToken()) {
            $headers[] = "Authorization: GoogleLogin auth=" . $this->authToken;
            $data = $message->getMessageBody();

            $buzz = new Browser();
            $buzz->getClient()->setVerifyPeer(false);
            $buzz->getClient()->setTimeout($this->timeout);
            $response = $buzz->post("https://android.apis.google.com/c2dm/send", $headers, http_build_query($data));

            return preg_match("/^id=/", $response->getContent()) > 0;
        }

        return false;
    }

    /**
     * Gets a valid authentication token
     *
     * @return bool
     */
    protected function getAuthToken()
    {
        $data = array(
            "Email"         => $this->username,
            "Passwd"        => $this->password,
            "accountType"   => "HOSTED_OR_GOOGLE",
            "source"        => $this->source,
            "service"       => "ac2dm"
        );

        $buzz = new Browser();
        $buzz->getClient()->setVerifyPeer(false);
        $buzz->getClient()->setTimeout($this->timeout);
        $response = $buzz->post("https://www.google.com/accounts/ClientLogin", array(), http_build_query($data));
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response->getContent(), $matches);
        $this->authToken = $matches[1];

        return true;
    }
}
