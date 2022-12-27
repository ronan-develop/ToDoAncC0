<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const DELETE = 'DELETE_TASK';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return $attribute == self::DELETE
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::DELETE => $this->deleteTask($user, $subject)
        };

    }

    // $subject => $task | $attribute => const
    private function deleteTask(UserInterface $user, mixed $subject): bool
    {
        if ($user == $subject->getUser()) {
            return true;
        }
        if ($this->security->isGranted("ROLE_ADMIN") && $subject->getUser() == null) {
            return true;
        }
        return false;
    }

    private function deleteAnonymousTask(UserInterface $user): bool
    {
        // Seuls les utilisateurs anonymes peuvent supprimer des tâches anonymes
        return $user->getRoles() == 'ROLE_ADMIN';
    }

}
