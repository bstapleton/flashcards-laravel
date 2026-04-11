<nav class="-mb-px flex space-x-8">
    <x-ui.table-nav-link route="flashcards.index" :count="$flashcards->total()">
        Active
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.fresh-learning" :count="$flashcards->total()" mobile-hidden="true">
        Fresh learning
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.intermediate-mastery" :count="$flashcards->total()" mobile-hidden="true">
        Intermediate mastery
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.high-mastery" :count="$flashcards->total()" mobile-hidden="true">
        High mastery
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.completely-mastered" :count="$flashcards->total()">
        Completely mastered
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.hidden" :count="$flashcards->total()">
        Hidden
    </x-ui.table-nav-link>
    <x-ui.table-nav-link route="flashcards.drafts" :count="$flashcards->total()">
        Drafts
    </x-ui.table-nav-link>
</nav>
