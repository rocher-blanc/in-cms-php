<?php

namespace App\Kernel\Middleware\Back;

use App\Kernel\AppContext;
use App\Kernel\Config;
use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Auth extends AbstractMiddleware
{
    public function __construct(private readonly Config $config = new Config()) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        return $this->handleRequest(fn() => $this->observe($request), $request, $handler);
    }

    private function observe(Request $request): void
    {
        $this->checkIPAccess();

        if (!$this->isLogged()) {
            if (!$this->isOnLoginPage($request)) {
                $this->Factory()->Response()->redirectLogin();
            } elseif ($this->isOnLoginPage($request)) {
                if (strtoupper($request->getMethod()) === 'POST') {
                    if ($this->checkAuth($request)) {
                        $this->Factory()->Response()->redirectUrlDestination();
                    }
                }
            }
        } else {
            if ($this->isOnLogoutPage($request) || $this->isBadIp()) {
                $this->logout();
                $this->Factory()->Response()->redirectLogin(false);
            } elseif ($this->isOnLoginPage($request)) {
                $this->Factory()->Response()->redirectHome();
            } else {
                $this->pushData();
            }
        }
    }

    private function checkIPAccess(): void
    {
        $security_lock_ip = \DB::for_table('param')
            ->where_equal('param_key', 'security_lock_ip')
            ->find_one();

        if ($security_lock_ip && $security_lock_ip->param_value == 1) {
            $auth = false;
            $security_list_ip = \DB::for_table('param')
                ->where_equal('param_key', 'security_list_ip')
                ->find_one();

            $list = $security_list_ip->param_value ?? '';
            foreach (explode("\n", $list) as $ip) {
                if (trim($ip) === $this->getIp()) {
                    $auth = true;
                }
            }

            if (!$auth) {
                $this->Factory()->Response()->show404();
                exit;
            }
        }
    }

    private function isLogged(): bool
    {
        $key = $this->config()->get('session');
        return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
    }

    private function checkAuth(Request $request): bool
    {
        $body     = $request->getParsedBody();
        $username = is_array($body) ? ($body[$this->config()->get('auth_username', 'username')] ?? '') : '';
        $password = is_array($body) ? ($body[$this->config()->get('auth_password', 'password')] ?? '') : '';

        if ($username !== '' && $password !== '') {
            $username = htmlentities($username, ENT_QUOTES);

            $user = \DB::for_table('user')
                ->where_equal('user_name', $username)
                ->find_one();

            if (is_object($user) && $user->user_name === $username && password_verify($password, $user->user_password)) {
                return $this->login($user);
            } else {
                \App\Kernel\Back\Log::getInstance()->alert(1, $username);
                return false;
            }
        }
        return false;
    }

    private function isOnLoginPage(Request $request): bool
    {
        return $request->getUri()->getPath() === $this->config()->get('login.url');
    }

    private function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function isBadIp(): bool
    {
        $key = $this->config()->get('session');
        return !(isset($_SESSION[$key]['ip']) && $this->getIp() === $_SESSION[$key]['ip']);
    }

    private function isOnLogoutPage(Request $request): bool
    {
        return $request->getUri()->getPath() === $this->config()->get('logout.url');
    }

    private function login(object $user): bool
    {
        $key = $this->config()->get('session');
        $_SESSION[$key] = [
            'id'        => $user->user_id,
            'username'  => $user->user_name,
            'group_id'  => $user->user_group_id,
            'name'      => ucfirst($user->user_fname) . ' ' . strtoupper($user->user_lname),
            'logged_in' => true,
            'ip'        => $this->getIp(),
        ];

        $this->pushData();

        $log = \App\Kernel\Back\Log::getInstance();
        $log->setUserId($user->user_id);
        $log->info(2);

        return true;
    }

    private function pushData(): void
    {
        $key  = $this->config()->get('session');
        $user = \DB::for_table('user')
            ->left_outer_join('user_group', ['user.user_group_id', '=', 'user_group.user_group_id'])
            ->where_equal('user_id', $_SESSION[$key]['id'])
            ->find_one();

        if (!$user) {
            $this->logout();
            $this->Factory()->Response()->redirectLogin();
        }

        AppContext::addGlobals([
            'user' => [
                'id'         => $user->user_id,
                'name'       => $user->user_name,
                'lname'      => $user->user_lname,
                'fname'      => $user->user_fname,
                'group_name' => $user->user_group_name,
                'group_id'   => $user->user_group_id,
                'admin'      => ($user->user_group_id == 1),
            ],
        ]);
    }

    private function logout(): void
    {
        $key = $this->config()->get('session');
        session_destroy();
        $_SESSION[$key] = [];
    }
}
