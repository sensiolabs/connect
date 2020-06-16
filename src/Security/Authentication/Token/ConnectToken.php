<?php

/*
 * This file is part of the SymfonyConnect package.
 *
 * (c) Symfony <support@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCorp\Connect\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCorp\Connect\Api\Entity\User;

/**
 * @author Marc Weistroff <marc.weistroff@sensiolabs.com>
 */
class ConnectToken extends AbstractToken
{
    private $accessToken;
    private $providerKey;
    private $apiUser;
    private $scope;

    public function __construct($user, $accessToken, User $apiUser = null, $providerKey, $scope = null, array $roles = [])
    {
        parent::__construct($roles);

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->setUser($user);
        $this->setAccessToken($accessToken);
        $this->apiUser = $apiUser;
        $this->providerKey = $providerKey;
        $this->scope = $scope;

        parent::setAuthenticated(\count($roles) > 0);
    }

    public function getRoles()
    {
        $user = $this->getUser();
        if ($user instanceof UserInterface) {
            return $this->getUserRoles($user);
        }

        if (method_exists(AbstractToken::class, 'getRoleNames')) {
            return parent::getRoleNames();
        }

        return parent::getRoles();
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;
    }

    public function getApiUser()
    {
        return $this->apiUser;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getCredentials()
    {
        return $this->accessToken;
    }

    public function __serialize(): array
    {
        return [$this->apiUser, $this->accessToken, $this->providerKey, $this->scope, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        list($this->apiUser, $this->accessToken, $this->providerKey, $this->scope, $parentState) = $data;

        parent::__unserialize($parentState);
    }

    private function getUserRoles(UserInterface $user)
    {
        $callBackMethod = 'getObjectUserRole';

        if (method_exists(AbstractToken::class, 'getRoleNames')) {
            $callBackMethod = 'getStringUserRole';
        }

        return array_map([$this, $callBackMethod], $user->getRoles());
    }
}
