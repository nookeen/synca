# Synca
Synca
Easily manage sync for MySQL table
Description: Data is added/updated on MASTER DB table constantly. The data from MASTER DB table needs to be synced with SLAVE DB(s) tables. Rather than deleting all entries from SLAVE DB(s) tables, Synca only inserts and updates entries which were added or modified.

## [Try the demo](http://nookeen.com/synca)

## Installation

Simply do this by [coming soon]...

MIT License.

A jQuery version is also included, but needs to be included manually.

## Usage:
Params:
- `param1` = id 
- `param` = the value you want to begin at
- `param1` = the value you want to arrive at
- `param1` = (optional) number of decimal places in number, default 0
- `param1` = (optional) duration in seconds, default 2
- `param1` = (optional, see demo) formatting/easing options object


```php
var number = new AddFriend("SomeElementYouWant", 2, 9);
if (!number.error) {
    number.start();
} else {
    console.error(number.error);
}
```

#### Other methods:
Toggle pause/resume:

```php
number.pauseResume();
```