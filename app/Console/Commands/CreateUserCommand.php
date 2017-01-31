<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateUserCommand
 */
class CreateUserCommand extends Command
{
    protected $signature = 'user:create 
        {--username=}
        {--email=}
        {--password=}
        {--role=* : User\'s role (User,Admin)}
        {--force}
    ';

    /**
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * CreateUserCommand constructor.
     *
     * @param Factory $validatorFactory
     */
    public function __construct(Factory $validatorFactory)
    {
        $this->validatorFactory = $validatorFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a user');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getOption('username');
        $email    = $input->getOption('email');
        $password = $input->getOption('password');
        $roles    = $input->getOption('role');

        $validator = $this->validatorFactory->make(
            $input->getOptions(),
            [
                'username' => ['required'],
                'email'    => ['required'],
                'password' => ['required'],
                'role'     => ['required', 'array'],
                'role.*'   => ['in:' . implode(',', Role::$rules)],
            ],
            [
                'role.*' => 'Valid user roles: ' . implode(',', Role::$rules),
            ]
        );

        if ($validator->fails()) {
            throw new InvalidOptionException(
                json_encode(
                    $validator->getMessageBag()
                              ->toArray()
                )
            );
        }

        User::create(
            [
                'username' => $username,
                'email'    => $email,
                'password' => $password,
                'roles'    => $roles,
                'active'   => true,
            ]
        );

        $output->writeln('Created!');
    }
}
