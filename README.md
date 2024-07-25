# Laravel flashcards

Small API for managing spaced repetition for learning different subjects. I wanted to make something relatively simple, but complicated enough that it would provide a small challenge for myself when writing basically the same thing in different languages :)

Disclaimer: Big ol' chunks of what's written below isn't written yet (including tests). It's provided under a Deal With It license.

## Flashcards

Create flashcards of different types:
1. statement - these have no answers to select, and just give you a fact/statement/whatever to digest
2. question - these have multiple answers and only one correct answer
3. multiple-choice - these have multiple answers with two or more correct answers

NOTE: probably going to replace statement as a true/false type question, so it can still be scored and difficulty updated accordingly.

Flashcards have tags, which you can think of like subjects or topics.

## Lessons (see note)

A flashcard can show up in many lessons. When creating a lesson, you can define which tags you want to see in that lesson. Other than that, the flashcards that show up are based on 'difficulty'.

NOTE: I'm probably going to abandon this concept to simplify the experience for the user.

### Difficulty

When you view a statement card, or answer one of the question cards, you can choose a difficulty. The difficulty dictates how soon before it will be eligible for showing up again:

- Easy: if it's been more than 30 minutes
- Medium: if it's been more than a week 
- Hard: if it's been more than a month
- Buried: it's been answered correctly on the hard difficulty - kudos!

But what if you want to start a lesson and there's no eligible flashcards left? It'll tell you, but you can always hit the random flashcard endpoint to grab a flashcard that won't get scored.

A lesson 'ends' when there's no more flashcards left in the 'pool' and you say you want to end the lesson. Otherwise, you can just come back later when there's more cards eligible; so if you don't care about the scoring, you can just run one indefinitely.

You can also hit the random flashcard endpoint to ignore the difficulty and scoring entirely.

### Wait, scoring?

Yep, the 'correctness' (remember there's multiple-choice ones) will create an average score for a lesson against all the flashcards you answered. This allows you to track (to a certain extent) your progress over time.

## Running it

1. Usual php stuff `composer install`
2. `php artisan l5-swagger:generate` to generate the API contract
3. `php artisan migrate:fresh` to set up the DB
4. `php artisan db:seed` if you can't be bothered making your own stuff and just want to have a poke around
5. `php artisan serve` and you can check it out in Swagger

## Anything else?

Yes, this is still very in progress and is (at time of writing) only representative of a few evenings tinkering with stuff after my day job.
