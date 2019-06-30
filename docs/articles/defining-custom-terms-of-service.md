# Defining custom Terms of Service (Rules)

UniEngine's repo provides an example of Terms of Service document, as presented on the ``rules.php`` page. However, to prevent admins from modifying the ``rules.definitions.default.lang`` (holding these rules) which is tracked by the repo, the engine accepts an alternative definition file, ignored by the git repo.

You can provide your custom Terms of Service by creating a language file (per each available language) called ``rules.definitions.custom.lang``. This file should use the same rules definition format as the default one, and will automatically replace the default definition. You have to define your own file per language, otherwise, the language that does not have its own custom definition will fall back to the default one.

## Custom rules definition format

The ``rules.definitions.custom.lang`` should adhere to the following rules definition format:

```
RuleSet_Schema (Object)
    - title (String) [optional]
        A title of the rules's set.
        This title, when provided, will be used to generate the ToS index and group headers.
    - elements (Array<RuleSet_Schema | RuleWithSubrules_Schema | String>) [required]
        Contains either nested RuleSets, rule objects with subrules or simple Strings.
        Strings will be displayed as text (HTML), while other two will be parsed recursively.
        There is no limit of how many nesting levels can be provided.

RuleWithSubrules_Schema (Object)
    - maintext (String) [required]
        The main content of the rule, displayed as text (HTML).
    - ul (Array<String>) [required]
        An array containing simple Strings, displayed in a non-numbered list.

```

Example:

```php

// The main definition variable, exposed in the "$_Lang" global
$_Lang['RulesDefinitions'] = [
    // Ruleset
    1 => [
        // Ruleset's title
        'title' => 'Ruleset A',

        // Ruleset's elements
        'elements' => [
            // Regular rule definitions.
            0 => 'This is an example rule.',

            1 => 'This is another example rule.',

            // Nested rules array definition,
            // no nesting title provided.
            2 => [
                'elements' => [
                    1 => 'Some nested rule.',

                    2 => 'Another nested rule.',
                ]
            ],

            // Nested rules array definition,
            // nesting title provided.
            3 => [
                'title' => 'Nested ruleset',
                'elements' => [
                    1 => 'Some named nested rule.',

                    2 => 'Another named nested rule.',
                ]
            ],

            // Rule with simple nested unordered subrules list,
            // the main content is defined in "maintext" property,
            // all nested subrules are defined in the "ul" property array.
            5 => [
                'maintext' => 'Rule with subrules:',

                'ul' => [
                    'First subrule.',

                    'Second subrule.',
                ]
            ],
        ]
    ],

    // Another ruleset, same schema used
    2 => [
        'title' => 'Ruleset B',
        'elements' => [
            0 => 'This is an example rule.',

            1 => 'This is another example rule.',
        ]
    ],
];

```
