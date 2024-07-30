# Laravel flashcards

Small API for managing spaced repetition for learning different subjects.

Disclaimer: Big ol' chunks of what's written below isn't complete yet (including tests). It's provided under a Deal With It license. There's a list of TODOs at the end of this readme.

## Flashcards

Create flashcards of different types:
1. statement - these have no answers to select, and just give you a fact/statement/whatever to digest
2. question - these have multiple answers and only one correct answer
3. multiple-choice - these have multiple answers with two or more correct answers

Flashcards have tags, which you can think of like subjects or topics.

### Difficulty

The difficulty of a card is based on how long since you last answered it.
If you answer correctly, then it will move to the next difficulty
If you answer incorrectly, then it will be reset to 'easy'

- Easy: You'll see it again in 30min+
- Medium: You'll see it again after 1w+ 
- Hard: You'll see it again after 1mo+
- Buried: You'll only see it in the graveyard - but you can 'resurrect' it back to easy if you want a refresher

### Scoring

Each time you provide a correct answer, you'll get an increase in score. In the case of a multiple choice, you'll only get a score increase if you get ALL the correct answers and NONE of the incorrect ones, so think carefully...

## Running it

1. Usual php stuff `composer install`
2. `php artisan l5-swagger:generate` to generate the API contract
3. `php artisan migrate:fresh` to set up the DB
4. `php artisan db:seed` will create a user for you and nothing else. If you want to use the importer command to generate some more realistic questions, this is probably enough seeding for you, otherwise:
   1. `php artisan seed --class=TagSeeder` will make up some tags for your flashcards to use
   2. `php artisan seed --class=FlashcardSeeder` will make some flashcards of different types for you to try out
5. `php artisan serve` and you can check it out in Swagger

To use the endpoints, you'll need to hit the login one first. The seeded user's email is `f2@test.com` and their password is very secure: `password`. I assume you know what you're doing with bearer tokens, or you're going to have Bad Time(tm).

### Importing data

If you want to import your own questions to try it out - something I recommend if you want to give this a proper test - there's a command you can run to do this.

First, you need a `questions.json` file. You can put it into the `/storage/import` directory so the command can find it.

Read through the following sections for formatting constraints, then when you're ready, you can run `php artisan app:import-flashcards {user}`, replacing `{user}` with the id of your seeded/created user in the DB.

#### For statement (i.e. true/false) questions
```json
[
    {
        "text": "Two plus two is seven",
        "type": "statement",
        "is_true": false,
        "explanation": "The correct answer is four.",
        "tags": [
            "mathematics"
        ]
    }
]
```

Statements require no answers, only a flag to stipulate whether the statement itself is true or not. 

If the statement is false, it is recommended to provide an `explanation` so the user can know what the correct answer would be if it were to come up as another type of question.

#### For multiple-choice (single correct answer), or multiple selection (multiple correct answers):
```json
[
    {
        "text": "What is two plus two?",
        "type": "single",
        "answers": [
            {
                "text": "Four",
                "explanation": "Yay maths",
                "is_correct": true
            },
            {
                "text": "Three",
                "explanation": "Boo, maths"
            }
        ],
        "tags": [
            "mathematics"
        ]
    }
]
```

You can set the type to `multiple` if you intend to have multiple answers in the set that are correct. The `explanation` is optional, but recommended for incorrect answers. It is useful for correct ones to provide more context if desired.

## The TODO list

- Endpoint to provide an answer
- Scoring system
  - Including segmentation by tag
  - Handling of 'correctness' matrix for multiple choice
- Updating the difficulty based on correct/incorrect answers
- Tests. So many tests.
- Error checking and handling for
  - Single answer multiple-choice questions where more than one answer has been flagged as correct
  - Any multiple choice question where no correct answer has been added
