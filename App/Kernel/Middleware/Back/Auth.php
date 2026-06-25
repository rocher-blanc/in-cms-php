<?php

namespace App\Kernel\Middleware\Back;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Auth extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    public function observe(): void
    {
        $this->checkIPAccess();

        if (!$this->isLogged()) {
            if (!$this->isOnLoginPage()) {
                $this->Factory()->Response()->redirectLogin();
            } elseif ($this->isOnLoginPage()) {
                if ($this->request()->isPost()) {
                    if ($this->checkAuth()) {
                        $this->Factory()->Response()->redirectUrlDestination();
                    }
                }
            }
        } elseif ($this->isLogged()) {
            if ($this->isOnLogoutPage() || $this->isBadIp()) {
                $this->logout();
                $this->Factory()->Response()->redirectLogin(false);
            } elseif ($this->isOnLoginPage()) {
                $this->Factory()->Response()->redirectHome();
            } elseif (!$this->isOnLogoutPage()) {
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
            }
        }
    }

    private function isLogged(): bool
    {
        $sessionKey = $this->app()->config('session');
        return isset($_SESSION[$sessionKey]) && !empty($_SESSION[$sessionKey]);
    }

    private function checkAuth(): bool
    {
        $username = $this->request()->post($this->app()->config('auth_username'));
        $password = $this->request()->post($this->app()->config('auth_password'));

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

    private function isOnLoginPage(): bool
    {
        return $this->request()->getPath() === $this->app()->config('login.url');
    }

    private function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    private function isBadIp(): bool
    {
        $sessionKey = $this->app()->config('session');
        if (isset($_SESSION[$sessionKey]['ip']) && $this->getIp() === $_SESSION[$sessionKey]['ip']) {
            return false;
        }
        return true;
    }

    private function isOnLogoutPage(): bool
    {
        return $this->request()->getPath() === $this->app()->config('logout.url');
    }

    private function login($user): bool
    {
        $sessionKey = $this->app()->config('session');
        $_SESSION[$sessionKey] = [
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
        $sessionKey = $this->app()->config('session');
        $user = \DB::for_table('user')
            ->left_outer_join('user_group', ['user.user_group_id', '=', 'user_group.user_group_id'])
            ->where_equal('user_id', $_SESSION[$sessionKey]['id'])
            ->find_one();

        if (!$user) {
            $this->logout();
            $this->Factory()->Response()->redirectLogin();
        }

        $this->app()->appendViewData([
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
        $sessionKey = $this->app()->config('session');
        session_destroy();
        $_SESSION[$sessionKey] = [];
    }
}
