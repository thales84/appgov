<?php

return [
    'errors' => [
        'not_draft' => 'This version can no longer be edited.',
        'not_under_review' => 'This version is not awaiting review.',
        'not_published' => 'Only a published version may be retired.',
        'incomplete_definition' => 'Add an effective date and at least one step before review.',
        'effective_date_order' => 'The effective date must be later than the latest published version.',
        'draft_already_exists' => 'Complete the existing draft before creating a new version.',
    ],
];
