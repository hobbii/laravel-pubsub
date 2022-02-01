# How to contribute to Hobbii/Laravel-PubSub

Thank you for contributing to Hobbii/Laravel-PubSub. Here are some tips to make this easy for you.

## The process for contributing

* Create a new branch from the development branch following the naming policy (See: Branch Naming)
* Code something awesome and run local unit tests and linting before committing
* Commit it with a commit message following the naming policy (See: Commit Naming) and push
* Open a PR that is relaying any information that is not otherwise stated in the linked issue/jira
* Enjoy your awesome code being delivered to production! 🚀

## Setup

Follow the setup guide in the [README](README.md)

## Testing

Please ensure all new features have tests that proves the functionality of the application.
If you have identified any edge cases that may be interesting to look out for,
please ensure those are properly test for.

You can run the test suite with `phpunit --testdox`

## Code Style

This project will be conforming to the current PSR standards, please refer to PSR-12 for code style.

## Branch Naming

All branch names must conform to following structure: `<type>/<ref>`
eg. `issue/123`, `story/CCC-123`, `test/cors-not-working`

* `<type>`: The type will refer to what kind of branch is this?
  If you are solving a Jira ticket it will be `story`, `task`, `bug`,
  if you are solving a GitHub issue it will be `issue`,
  if you are just testing out something it will be `test`
* `<ref>`: A reference to where this problem statement was documented eg. `XY-500` for a jira story by Team XY
  or `123` for a GitHub Issue with id 123

## Commit Messages

All commit messages must conform to following structure: `<ref> <description>`
eg. `#123 Saved the planet`,  `CCC-123 Saved the planet`

* `<ref>`: A reference to where this problem statement was documented eg. `XY-500` for a jira story by Team XY
  or `#123` for a GitHub Issue with id 123
* `<description>`: A brief but descriptive summarization of what happened in this commit.
  Please read [How To Write Git Commit Message](https://chris.beams.io/posts/git-commit/)

## Merging a Pull Request

In order to merge a PR you must ensure following things holds

* The build has passed
* Tests are covered
* Code has been approved by a non-co-author (you?)
* Code is awesome! 🌟
