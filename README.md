#.publisher

> Package for publishing or linking files from composer's vendor dependencies.

- <a href="install">Install</a>
- <a href="schemas_and_syntax">Supported schemas and syntax</a>
- <a href="usage_package">Usage in package</a>
- <a href="usage_project">Usage in project</a>

##Install

**.publisher** can be installed from composer: 

```json
{
    "require": {
        "dubpub/publisher": "1.*"
    }
}
```

Once **.publisher** is installed, it's executable is available from <code>vendor/bin</code> folder, simply run command to check 
the installation:

```bash
$> vendor/bin/publisher
```
 
##Schemas and syntax
 
By default publisher supports following formats: *.php, *.json, *.yaml, *.yml.
Schema must consists of 3 levels - package names, which contains group names, which contains file notations.

- package name
    - file group name
        - file notation
   
File notation examples:

<table width="100%">
    <thead>
        <tr>
            <th width="35%">Notation</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>assets</code></td>
            <td>
                Will copy <code>assets/</code> folder, from package's directory and copy it into configured publish 
                folder.
            </td>
        </tr>
            <td><code>assets/styles.css</code></td>
            <td>
                Will copy <code>assets/styles.css</code>, that's located on package's folder 
                and publish it into configured publish folder.
            </td>
        </tr>
        <tr>
            <td><code>assets/ -> public/</code></td>
            <td>
                Will copy <code>assets/</code> folder, from package's directory and copy it into <code>public/</code> directory configured 
                publish folder.
            </td>
        </tr>
        <tr>
            <td><code>assets -> {public,web}/</code></td>
            <td>    
                Will look up for <code>public/</code> or <code>web/</code> path. If neither are available, first from the list will be 
                created. And <code>assets/</code> folder, from package's directory will be copied into resulting directory.
            </td>
        </tr>
        <tr>
            <td><code>assets/styles/* -> {public,web}/css/</code></td>
            <td>
                Will look up for <code>public/</code>" or <code>web/</code> path. If neither are available, first from the list will be 
                created. And all files from folder <code>assets/styles/</code> of package's directory will be copied into 
                `{resulting directory}/css/`.
            </td>
        </tr>
        <tr>
            <td><code>@path/to/my/link</code></td>
            <td>
                Will create link of package's <code>path/to/my/link</code> and place it in configured publish path. 
            </td>
        </tr>
        <tr>
            <td><code>@path/to/my/* -> bin/</code></td>
            <td>
                Will create links of package's every file from <code>path/to/my/</code> folder and place it in 
                <code>bin/</code> of configured publish path. 
            </td>
        </tr>
    </tbody>
</table>

####PHP example:
```php
<?php // .publisher.php
return [
    "myvendor/mypackage" => [
        "assets" => [
            "assets/css/* -> {public/web}/assets/styles/"
        ],
        "bin" => [
          "@bin/executableFile -> bin/"
        ]
    ]
];
```
####JSON example:
```json
{
  "myvendor/mypackage": {
    "assets": [
      "assets/css/* -> {public/web}/assets/styles/"
    ],
    "bin": [
      "@bin/executableFile -> bin/"
    ]
  }
}
```
####YML,YAML example:
yml, yaml:

```yml
myvendor/mypackage:
    assets:
        - 'assets/css/* ->  {public/web}/assets/styles/'
    bin:
        - '@bin/executableFile -> bin/'
```

##Usage in package
Simply initiate .publisher file an fill it with contents you need, according to examples above and place it into folder 
where your project's `composer.json` is located. Note, that .publisher will not work if `composer.json` file or 
`vendor/` folder don't exists.

##Usage in project
After installing .publisher into your project you need to generate .publisher file or create it manually. Use `init` 
command to generate .publisher file:

```bash
$MyProject> vendor/bin/publisher init
```

`init` command will generate .publisher file and perform scanning `vendor/` folder for other .publisher files to merge 
them into new generated one.

If you want to generate .publisher file with specific format(default is php), you need to specify it:

```bash
$MyProject> vendor/bin/publisher init
```

Note, that every `init` call does not recreate or overwrite your (project/package)'s file section, .publisher 
simply merges and updates other sections.

After your .publisher file is generated it's ready to use:

For publishing every .publisher dependency:
```bash
$MyProject> vendor/bin/publisher publish
```
or:
```bash
$> vendor/bin/publisher publish "*"
```

For publishing specific package:
```bash
$> vendor/bin/publisher publish acmevendor/acmepackage
```

For publishing specific package's group:
```bash
$> vendor/bin/publisher publish acmevendor/acmepackage assets
```

For publishing specific packages' groups:
```bash
$> vendor/bin/publisher publish acmevendor/acmepackage,acmevendor1/acmepackage1 assets,configs
```
