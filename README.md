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
4. `php artisan db:seed` if you can't be bothered making your own stuff and just want to have a poke around
5. `php artisan serve` and you can check it out in Swagger

## The TODO list

- Endpoint to provide an answer
- Scoring system
  - Including segmentation by tag
  - Handling of 'correctness' matrix for multiple choice
- Updating the difficulty based on correct/incorrect answers
- Tests. So many tests.
- Replacement of statement cards to something akin to true/false
