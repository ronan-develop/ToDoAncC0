# First steps

Lets configuring your development environment **in less than 5 minutes**.

## Getting Started

Get started by **cloning the repository on [GitHub](https://github.com/ronan-develop/ToDoAncC0)**.

### What you'll need

- [Node.js](https://nodejs.org/en/download/) Version 16.14 or above.
- [Composer](https://getcomposer.org/) To install dependencies.
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git) To clone or contribute.
- [Symfony CLI](https://symfony.com/download) We're using it in this project, you are free to choose.

## Install Symfony CLI
you can refer to the [SYMFONY documentation](https://symfony.com/download) to perform the installation according to your
operating system.

## Install Node
you can refer to the [Node documentation](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) to perform
the installation according to your operating system.

## Install Git
you can refer to the [Git documentation](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git) to perform the
installation according to your operating system.

## How to download and configure 
Type this command in your working folder when Git is installed:

```bash
git clone https://github.com/ronan-develop/ToDoAncC0.git
```

You can type this command into Command Prompt, Powershell, Terminal, Git Bash, or any other integrated terminal of your
code editor.

## Install dependencies

Run composer command:

```bash
composer install
```

## Environment configuration

For your local environment, create `.env.local` , copy and paste the `.env` content. Configure it according to your
development requirements and set `APP_ENV=dev`.

In production, ToDoAndCo is using a mysql database.

In your CLI, juste type :

```bash
composer prepare-dev
```
A configuration script will be launched by Composer with a database full of fake data and ready for the development.
