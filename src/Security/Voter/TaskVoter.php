<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const DELETE = 'TASK_DELETE';
    public const EDIT = 'EDIT_TASK';
    public const ANONYMOUS = 'TASK_ANONYMOUS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE, self::EDIT, self::ANONYMOUS])
            && $subject instanceof \App\Entity\Task;
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
            self::DELETE => $this->deleteTask($user, $subject),
            self::EDIT => $this->editTask($user, $attribute),
            self::ANONYMOUS => $this->deleteAnonymousTask($user, $attribute),
            default => false,
        };

    }

    // $subject => $task | $attribute => const
    private function deleteTask(UserInterface $user, mixed $subject): bool
    {
        if ($user->getId() == $subject->getUser()->getId()) {
            return true;
        }

        // Si l'utilisateur n'a pas le rôle "ROLE_ADMIN", vérifiez s'il est propriétaire de la tâche
        return $user->getId() === $subject->getOwner()->getId();
    }

    private function deleteAnonymousTask(UserInterface $user, string $attribute): bool
    {
        // Seuls les utilisateurs anonymes peuvent supprimer des tâches anonymes
        return $user->getRoles() == 'ROLE_ADMIN';
    }

    private function editTask(UserInterface $user, string $attribute)
    {
    }

}
