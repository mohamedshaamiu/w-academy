<?php

/*
 | Public-facing marketing copy.
 |
 | SPEC.md §3.6 forbids fabricating academy content. Keys marked
 | DEMO COPY below were authored to give the demo a finished public site and
 | are NOT customer-supplied — they are tracked in BACKLOG-NEW.md (NEW-5) and
 | must be confirmed or replaced by W-Academy before go-live. Keys carrying
 | [EN CONTENT PENDING] are genuinely awaiting the customer.
 */
return [
    'hero' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'label' => 'Academy highlights',
        'previous' => 'Previous slide',
        'next' => 'Next slide',
        'go_to' => 'Go to slide',
        'primary_cta' => 'Read the framework guide',
        'secondary_cta' => 'Talk to the academy',

        'slides' => [
            'pillars' => [
                'eyebrow' => 'Youth football · Maldives',
                'title' => 'Four pillars. One player.',
                'body' => 'We coach the whole young person — at home, at school, in faith and on the pitch.',
            ],
            'framework' => [
                'eyebrow' => 'The behaviour framework',
                'title' => 'Discipline you can read in advance',
                'body' => 'Every consequence is written down, graduated, and shared with parents before the season begins. No surprises.',
            ],
            'families' => [
                'eyebrow' => 'For families',
                'title' => 'Guardians see everything',
                'body' => 'Schedule, attendance and the signed agreement — in Dhivehi or English, on any phone.',
            ],
        ],
    ],

    'intro' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'Character first, football always',
        'body' => 'W-Academy trains young footballers in the Maldives aged 5 to 18. Players are grouped into age squads, train to a published weekly schedule, and are held to a single behaviour framework built on four life pillars — home, school, religion and sport.',
    ],

    'vision' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'Our vision',
        'body' => 'Maldivian children who carry the same discipline off the pitch that they show on it — respectful at home, present at school, steady in their faith, and fair in their sport.',
    ],

    'mission' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'Our mission',
        'body' => 'To give every player consistent coaching, a clear and published code of behaviour, and a guardian who is kept fully informed — so that progress on the pitch is never separated from progress in life.',
    ],

    'values' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'What we hold to',
        'items' => [
            'discipline' => [
                'title' => 'Discipline',
                'body' => 'The strike ladder is published, graduated, and applied the same way for every player.',
            ],
            'respect' => [
                'title' => 'Respect',
                'body' => 'Coaches, players and parents are held to the same standard of conduct.',
            ],
            'consistency' => [
                'title' => 'Consistency',
                'body' => 'Sessions run to a schedule, attendance is recorded every session, and the record is open to guardians.',
            ],
            'partnership' => [
                'title' => 'Partnership',
                'body' => 'Nothing about a child is decided without their guardian — the agreement is signed before a player takes the field.',
            ],
        ],
    ],

    'pillars' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'The four life pillars',
        'body' => 'Behaviour at W-Academy is assessed across four areas of a young person’s life, not just their football.',
        'cta' => 'See the full framework guide',
    ],

    'how' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'How to join',
        'body' => 'Enrolment is arranged through the academy office. The whole process is four steps.',
        'steps' => [
            'visit' => [
                'title' => 'Visit the office',
                'body' => 'Enrolment is arranged in person. There is no online sign-up form.',
            ],
            'record' => [
                'title' => 'Records are created',
                'body' => 'The academy registers the player and links their guardian.',
            ],
            'sign' => [
                'title' => 'The guardian signs',
                'body' => 'The discipline agreement is read in Dhivehi or English and signed digitally.',
            ],
            'train' => [
                'title' => 'Training begins',
                'body' => 'The player joins an age squad and the weekly schedule opens in the portal.',
            ],
        ],
    ],

    'coaches' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6). Names, photos and
        // specialisations come from the coach records the academy creates.
        'heading' => 'Meet the coaches',
        'body' => 'Every squad trains under an assigned academy coach.',
        'role_fallback' => 'Academy coach',
        'since' => 'With the academy since :year',
    ],

    'cta' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'heading' => 'Ready to enrol your child?',
        'body' => 'Enrolment, squad placement and questions about the agreement are all handled at the academy office.',
        'action' => 'Contact the academy',
    ],

    'home' => [
        'enrolment_notice' => 'Enrolment is arranged through the academy office — there is no online sign-up. Please visit or call us to register your child.',
    ],

    'about' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'title' => 'About the academy',
        'lead' => 'A youth football academy in the Maldives, run on one published behaviour framework.',

        'story_heading' => 'Who we are',
        'story_body' => 'W-Academy is a youth football academy in the Maldives. We coach players aged 5 to 18 in age-based squads, on a weekly schedule set by the academy and delivered by assigned coaches. Every session is registered, attendance is recorded, and that record is visible to the player’s guardian.',

        'framework_heading' => 'How we work',
        'framework_body' => 'Behaviour is assessed across the four life pillars — home, school, religion and sport. What happens when a player falls short is set out in advance on a graduated strike ladder: at every level, the action the coach takes and the parent’s role are written down before anyone needs them.',

        'who_heading' => 'Who the academy is for',
        'who_body' => 'Players aged 5 to 18 whose families are prepared to sign the academy’s discipline agreement and stay involved. The agreement is signed by the guardian, in the language they choose, before a player takes the field.',

        'portal_heading' => 'One portal, three views',
        'portal_body' => 'The same records, shown to each person only as far as it concerns them.',
        'portal' => [
            'guardian' => [
                'title' => 'Guardians',
                'body' => 'Sign the agreement, then follow each child’s squad, schedule and attendance.',
            ],
            'student' => [
                'title' => 'Players',
                'body' => 'Your own schedule and your own attendance — never anything about another player.',
            ],
            'coach' => [
                'title' => 'Coaches',
                'body' => 'Run today’s sessions and mark attendance from a phone at the pitch.',
            ],
        ],

        'language_heading' => 'Dhivehi first',
        'language_body' => 'Every screen is available in Dhivehi and English. Dhivehi is the default, and the language can be switched from the header of any page — including the agreement itself, so a guardian reads it in the language they actually sign in.',
    ],

    'contact' => [
        'title' => 'Contact Us',
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'lead' => 'Enrolment, squad placement and questions about the agreement are all handled at the academy office.',
        'address_heading' => 'Address',
        'address_value' => '[EN CONTENT PENDING]',
        'phone_heading' => 'Phone',
        'phone_value' => '[EN CONTENT PENDING]',
        'office_hours_heading' => 'Office Hours',
        'office_hours_value' => 'Sunday–Thursday, 9:00–16:00',
    ],

    'footer' => [
        // DEMO COPY — CLIENT TO CONFIRM (SPEC.md §3.6)
        'blurb' => 'A youth football academy in the Maldives, built on four life pillars.',
        'explore_heading' => 'Explore',
        'reach_heading' => 'Get in touch',
    ],
];
