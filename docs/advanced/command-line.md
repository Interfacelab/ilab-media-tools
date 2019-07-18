# Command Line
Media Cloud provides a variety of commands that you can use with [WP CLI](https://wp-cli.org) command line tool.

&nbsp;
## Cloud Storage Commands
### Import
```bash
wp mediacloud import [--limit=<number>] [--offset=<number>] [--page=<number>]
```
This command will import items in your media library to cloud storage.

#### Arguments
Argument | Type | Optional | Description
-------- | ---- | -------- | -----------
limit | number | yes | Limits the number of items to be processed, used with `offset` or `page` to process batches.
offset | number | yes | The starting index to return a range of results, cannot be used with `page`.
page | number | yes | The starting index to return a range of results in terms of pages, cannot be used with `offset`.  The page index start at 1.  For example, specify a `limit` of `100` and a `page` of `2` would return results 100-200.

&nbsp;

### Regenerate
```bash
wp mediacloud regenerate [--limit=<number>] [--offset=<number>] [--page=<number>]
```
This command will regenerate thumbnails for items in the media library.

#### Arguments
Argument | Type | Optional | Description
-------- | ---- | -------- | -----------
limit | number | yes | Limits the number of items to be processed, used with `offset` or `page` to process batches.
offset | number | yes | The starting index to return a range of results, cannot be used with `page`.
page | number | yes | The starting index to return a range of results in terms of pages, cannot be used with `offset`.  The page index start at 1.  For example, specify a `limit` of `100` and a `page` of `2` would return results 100-200.

&nbsp;

### Unlink
```bash
wp mediacloud unlink [--limit=<number>] [--offset=<number>] [--page=<number>]
```
This command will unlink items in the media library from cloud storage.  Note that this does not copy media down from cloud storage, it simply removes the cloud metadata.

#### Arguments
Argument | Type | Optional | Description
-------- | ---- | -------- | -----------
limit | number | yes | Limits the number of items to be processed, used with `offset` or `page` to process batches.
offset | number | yes | The starting index to return a range of results, cannot be used with `page`.
page | number | yes | The starting index to return a range of results in terms of pages, cannot be used with `offset`.  The page index start at 1.  For example, specify a `limit` of `100` and a `page` of `2` would return results 100-200.

&nbsp;

## Dynamic Image Commands
### Clear Cache
```bash
wp dynamicImages clearCache
```
Clears the dynamic images file cache.

&nbsp;

