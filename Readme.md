# Chat server

Simple PHP chat server implemented with [Ratchet](http://socketo.me/)

Setup instructions:

1. Clone the repository: `git clone <repo-url>`
2. Install the dependencies: `composer install`
3. Start the soket server: `php index.php`

By default the socket is set to **8080**. To set a different port use the `-p` flag (eg: `php index.php -p 4000`).

At the moment only one thread is supported.

## Message Types

At the moment server supports the follwoing message types:

* `join` - Sent to the existing users when someone new joins the conversation
* `leave` - Sent when someone disconnects
* `greeting` - Sent to a newly connected user. Contains the current user (created by the server), message history and the user list (including current user)
* `message` - Used for new messages
* `edit` - Used to notify the users when message is edited
* `delete` - Used to notify the users when message is deleted
* `username-update` - Used to notify the users when username is updated
