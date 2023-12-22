<?php

namespace App\Tests\Security\Voter;

use App\Entity\Site;
use App\Entity\User;
use App\Security\Voter\UserSiteAccessVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserSiteAccessVoterTest extends TestCase
{
    public function userWithAccessCanVoteOnSite(): void
    {
        $user = $this->createMock(User::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $voter = new UserSiteAccessVoter($requestStack);

        $this->assertFalse($voter->voteOnAttribute('ROLE_USER', $site, $token));
    }

    public function userCannotVoteOnNonSiteSubject(): void
    {
        $user = $this->createMock(User::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $token->method('getUser')->willReturn($user);
        $requestStack->method('getCurrentRequest')->willReturn(null);

        $voter = new UserSiteAccessVoter($requestStack);

        $this->assertFalse($voter->voteOnAttribute('ROLE_USER', new \stdClass(), $token));
    }

    public function userCannotVoteWithInvalidRole(): void
    {
        $user = $this->createMock(User::class);
        $site = $this->createMock(Site::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);

        $token->method('getUser')->willReturn($user);
        $request->method('get')->willReturn($site);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $voter = new UserSiteAccessVoter($requestStack);

        $this->assertFalse($voter->voteOnAttribute('INVALID_ROLE', $site, $token));
    }
}
```        $site = $this->createMock(Site::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);

        $token->method('getUser')->willReturn($user);
        $request->method('get')->willReturn($site);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $voter = new UserSiteAccessVoter($requestStack);

        $this->assertTrue($voter->voteOnAttribute('ROLE_USER', $site, $token));
    }

    public function userWithoutAccessCannotVoteOnSite(): void
    {
        $user = $this->createMock(User::class);
        $site = $this->createMock(Site::class);
        $token = $this->createMock(TokenInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);

        $token->method('getUser')->willReturn($user);
        $request->method('get')->willReturn($site);
