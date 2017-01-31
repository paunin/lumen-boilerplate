<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Tymon\JWTAuth\JWTAuth;

/**
 * Command to create JWT token for user.
 */
class CreateJwtTokenCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'user:create-token 
                            {username : Username} 
                            {ttl : token TTL, unit is day}';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create JWT token for given username';

    /**
     * @var JWTAuth
     */
    private $jwtAuth;

    /**
     * @var Factory
     */
    private $validationFactory;

    /**
     * CreateJwtTokenCommand constructor.
     *
     * @param JWTAuth $jwtAuth
     * @param Factory $validationFactory
     */
    public function __construct(JWTAuth $jwtAuth, Factory $validationFactory)
    {
        $this->jwtAuth           = $jwtAuth;
        $this->validationFactory = $validationFactory;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function handle(): int
    {
        $arguments = $this->argument();

        /** @var Validator $validator */
        $validator = $this->validationFactory->make(
            $arguments,
            [
                'username' => ['required', 'exists:users,username'],
                'ttl'      => ['required', 'integer', 'min:1'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->getMessageBag()
                               ->getMessages() as $option => $messages) {
                $this->error($option . ': ' . implode(', ', $messages));
            }

            return 1;
        }

        /** @var User $user */
        $user = User::where('username', '=', $arguments['username'])
                    ->firstOrFail();

        $ttlInMinutes = ((int) $arguments['ttl']) * 1440;
        $this->jwtAuth->factory()
                      ->setTTL($ttlInMinutes);

        $token = $this->jwtAuth->fromUser($user);

        $this->info($token);

        return 0;
    }
}
