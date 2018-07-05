# Pre 1.0

* Write docblocks to the head of a single class

# 1.0

* use the start/end tags to detect changes.

# 1.0.1 to 1.0.4

* Minor code changes and cleanups to get code quality up
* Added docs at [github.io](https://axyr.github.io/ideannotator)
* Added scrutinizer
* Added CodeClimate
* Improved to better match SilverStripe Module standards


# 2.0

* Implemented phpDocumentor
* Remove start/end tags
* All classes in a single file annotated
* Added DataRecord and Data() annotation for _Controller methods

# 2.0.1 to 2.0.4
* Minor fixes 
* Check for Controller::curr(); to support cli
* Correct environment checking
* Added warning messages when a file is not writable or class defenition is misspelled

# 2.0.5
* Support dot notations in relations

# 3.0 beta-1
* Support for SilverStripe 4

# 3.0 rc-1
* Updated support for SilverStripe 4
* Added support for the `through` method

# 3.0 rc-2
* Fixed bug trimming too much whitespace in rc-1
* Support for short classnames instead of FQN
* ~~require_once in tests no longer needed~~ Nope, still needed