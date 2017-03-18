wooMoodleTokenEnrolment
=====================

A plugin designed to allow a wooCommerce digital download to generate enrolment tokens using the [Moodle Token Enrolment](https://github.com/frumbert/moodle-token-enrolment) set of plugins. The tokens themselves are just 10-character random strings that let learners register and enrols them directly to a given Moodle course. Only people with a token can register.

Not much use if you don't have Moodle with the token enrolment plugins installed and activated, or WooCommerce on your Wordpress installation ...

When a user buys a product and gets through the checkout, the digital download actually performs some code and generates, from your Moodle installation, a bunch of access tokens based on parameters inside the digital download file. Your customer can then hand these out to their learners.

How to make downloads
---------------------------------

WooCommerce expects a digital download to be a file, not a function call. Make a plain text file that ends in `-wmte.txt` (e.g. `mycourse2-wmte.txt`) for your "digital download" product and format it so that each option has a line of its own, with the name on the left and the value after an equals sign. You could then have different products containing different files.

    course=myCourse2
    cohort=April2017
    seats=10

Possible values you can use are:

    course - the IDNUMBER of the course in Moodle (this is NOT the row id)
    cohort - the IDNUMBER of the cohort in Moodle that users of this token will be added to (created automatically if it's not found). Use the cohort for managing users, or allowing them access to multiple courses using the cohort membership provider, or combine with other plugins like https://github.com/moodlehq/moodle-enrol_groupsync to greate groups (default: local_token_enrolment)
    seats - the actual number of tokens you want to generate, from 1 to 500 (default: 1)
    places - the number of times each token can be used, from 1 to 500 (default: 1)
    expiry - a unix_timestamp which represents the date that the tokens are no longer considered valid (default: zero for never)
    prefix - a 0-4 letter prefix you want your tokens to start with (default: empty)

You are required to specify at least the `course`. You'd only then get a 1 seat, 1 place token. See the Moodle web service documentation for the local_token_generatetokens webservice for more information on default values.

Licence:
-----------

MIT
