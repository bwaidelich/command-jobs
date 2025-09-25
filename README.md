# Command Jobs

Coordinate execution of one-time commands

This library can be used to enqueue arbitrary commands that need to be executed on all stages, this includes:

- database migrations
- data fixtures / transformations
- one-time bug fixes
- ...

## Usage

Install via [composer](https://getcomposer.org):

```bash
composer require wwwision/command-jobs
```

and run pending command jobs via

```bash
vendor/bin/command-jobs run
```

### Options

#### `root`

By default, the `run` command will create a `command-jobs` folder in the current directory, that contains YAML files for [Command Definitions](#command-definition), [Command Jobs](#command-job), [Command Results](#command-results) and a `.gitignore` file that excludes Command Results from being added to VCS.
The `--root` option allows to specify a different root directory for those three files:

```bash
vendor/bin/command-jobs run --root=/some/other/folder
```

#### `stop-on-error`

Commands that fail or return an exit code other than `0` lead to an error (and the `run` command to exit with an error code as well). By default, remaining pending [Command Jobs](#command-job) will still be executed.
It might make sense to stop processing of those jobs entirely in this case:

```bash
vendor/bin/command-jobs run --stop-on-error
```

#### default options

The `run` command supports default options that control verbosity and styling of the output:

```
Usage:
  vendor/bin/command-jobs run [options]

Options:
  -r, --root=ROOT       Root directory
      --stop-on-error   Intercept execution upon errors
  -h, --help            Display help for the given command. When no command is given display help for the list command
      --silent          Do not output any message
  -q, --quiet           Only errors are displayed. All other output is suppressed
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Concepts

### Command Definition

In order for commands to be enqueued/executed, they have to be explicitly defined. This prevents invalid or potentially dangerous commands to be executed automatically. Furthermore, it allows commands to have a description and to be reused.
A Command Definition consists of:

- `id` – a unique string, that identifies it. It might make sense to use dots for namespaces to group definitions (e.g. `"db.migrate.products-initial"`)
- `description` – a string, that explains the intention of a command (e.g. `"Creates required product tables – this command is idempotent"`)
- `cmd` – the actual command to execute. This is represented as an array that consists of an element for the command itself and one for each argument (e.g.`["some-command", "arg1", "\"arg2 with spaces\""]`)

The `add-definition` CLI command can be used to add Command Definitions:

```
Usage:
  vendor/bin/command-jobs add-definition [options] [--] [<id> [<description> [<cmd>...]]]

Arguments:
  id                    ID of the command definition
  description           Description of the command definition
  cmd                   The command to execute (command and arguments, separated by spaces)

Options:
  -r, --root=ROOT       Root directory
  -h, --help            Display help for the given command. When no command is given display help for the list command
      --silent          Do not output any message
  -q, --quiet           Only errors are displayed. All other output is suppressed
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### Command Job

To enqueue commands, a corresponding Command Job has to be added. Each Command Job is executed once (unless if fails) when `vendor/bin/command-jobs run` is invoked.
A Command Job consists of:

- `id` – a unique string, that identifies it. This is the timestamp at the time of creation in the format `YmdHis` (e.g. `20250925151814`)
- `commandDefinitionId` – the id of an existing [Command Definition](#command-definition)

The `add-job` CLI command can be used to add Command Jobs:

```
Usage:
  vendor/bin/command-jobs add-job [options] [--] [<definition>]

Arguments:
  definition            ID of the command definition

Options:
  -r, --root=ROOT       Root directory
  -h, --help            Display help for the given command. When no command is given display help for the list command
      --silent          Do not output any message
  -q, --quiet           Only errors are displayed. All other output is suppressed
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### Command Result

The result of any execution of a [Command Jobs](#command-job) is stored as a Command Result.
By default, the results are stored in a `commandResults.yaml` file.

> [!IMPORTANT]  
> The `commandResults.yaml` file is specific to a single installation, it must not be added to the VCS
> It is therefore excluded via `.gitignore` file by default

A Command Result consists of:

- `commandJobId` – the id of the [Command Jobs](#command-job) that was executed
- `commandDefinitionId` – the id of the corresponding [Command Definition](#command-definition)
- `executionTime` – date and time of the execution
- `executionDurationInMilliseconds` – execution runtime in milliseconds
- `success` – a boolean flag that is `true` if the exit code of the command was `0` and otherwise `false`
- `output` - the string output of the command – or the exception message if it failed

## Contribution

Contributions in the form of [issues](https://github.com/bwaidelich/command-jobs/issues) or [pull requests](https://github.com/bwaidelich/command-jobs/pulls) are highly appreciated

## License

See [LICENSE](./LICENSE)
