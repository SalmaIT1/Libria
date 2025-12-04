<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user account',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Admin email')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Admin password')
            ->addOption('first-name', null, InputOption::VALUE_OPTIONAL, 'First name')
            ->addOption('last-name', null, InputOption::VALUE_OPTIONAL, 'Last name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        // Get email
        $email = $input->getOption('email');
        if (!$email) {
            $question = new Question('Enter admin email: ');
            $email = $helper->ask($input, $output, $question);
        }

        if (!$email) {
            $io->error('Email is required!');
            return Command::FAILURE;
        }

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->warning("User with email '{$email}' already exists. Updating to admin role...");
            $existingUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
            $this->entityManager->flush();
            $io->success("User '{$email}' has been updated to admin role!");
            return Command::SUCCESS;
        }

        // Get password
        $password = $input->getOption('password');
        if (!$password) {
            $question = new Question('Enter admin password: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);
        }

        if (!$password || strlen($password) < 6) {
            $io->error('Password is required and must be at least 6 characters!');
            return Command::FAILURE;
        }

        // Get first name
        $firstName = $input->getOption('first-name');
        if (!$firstName) {
            $question = new Question('Enter first name (optional): ', '');
            $firstName = $helper->ask($input, $output, $question) ?: null;
        }

        // Get last name
        $lastName = $input->getOption('last-name');
        if (!$lastName) {
            $question = new Question('Enter last name (optional): ', '');
            $lastName = $helper->ask($input, $output, $question) ?: null;
        }

        // Create admin user
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success("Admin user '{$email}' has been created successfully!");

        return Command::SUCCESS;
    }
}

