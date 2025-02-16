### Changing the Singular and Plural Model Names

```php
protected static ?string $modelLabel = 'عميل';
protected static ?string $pluralModelLabel = 'عملاء';
```

This property is used to specify the singular name of the model. It can be used in any resource to change the default singular name of the model.

This property is used to specify the plural name of the model. It can be used in any resource to change the default plural name of the model.


### Generating the Model, Migration, and Factory at the Same Time

If you'd like to save time when scaffolding your resources, Filament can also generate the model, migration, and factory for the new resource at the same time using the `--model`, `--migration`, and `--factory` flags in any combination:

```bash
php artisan make:filament-resource Customer --model --migration --factory
```
### Adding a View Page to an Existing Resource

To add a View page to an existing resource, create a new page in your resource's Pages directory:

```bash
php artisan make:filament-page ViewUser --resource=UserResource --type=ViewRecord
```

This command will generate a new View page for the specified resource.

### Listening to the Queue

To start listening to the queue, you can use the following Artisan command:

```bash
php artisan queue:work
```

This command will start processing jobs in the queue.


### Starting the Reverb Service

To start the Reverb service, you can use the following Artisan command:

```bash
php artisan reverb:start
```

This command will initiate the Reverb service.


### Generating Shield

To generate the Shield for your Filament resources, you can use the following Artisan command:

```bash
php artisan shield:generate --resource | --all
```

This command will create the necessary Shield files for your resources.


### Creating a Relation Manager

To create a relation manager, you can use the `make:filament-relation-manager` command:

```bash
php artisan make:filament-relation-manager CategoryResource posts title
```

- `CategoryResource` is the name of the resource class for the owner (parent) model.
- `posts` is the name of the relationship you want to manage.
- `title` is the name of the attribute that will be used to identify posts.
