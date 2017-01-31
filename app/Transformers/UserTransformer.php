<?php
declare(strict_types=1);

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

/**
 * Class UserTransformer
 */
class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id'        => $user->getId(),
            'username'  => $user->getUsername(),
            'email'     => $user->getEmail(),
            'active'    => $user->isActive(),
            'roles'     => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()
                                ->format(DATE_RFC3339),
            'updatedAt' => $user->getUpdatedAt()
                                ->format(DATE_RFC3339),
        ];
    }
}
