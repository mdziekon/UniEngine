# "One offs" utility

This utility consists of a list of one off scripts, meant to be executed only once in some special ocasions (eg. in case of a manual migration action). The scripts runner will try to keep track of what scripts were run in the past (by saving to `config/applied-one-offs.json` file).

## Available commands

- List all scripts (and return their status)
- Run a script
