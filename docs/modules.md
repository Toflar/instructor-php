# Modules

> NOTE: This is a work in progress. The documentation is not complete yet.

Modules are a way to encapsulate structured processing logic and data flows. They are inspired by DSPy and TensorFlow modules.

Instructor comes with a set of built-in modules, which can be used to build more complex processing pipelines. You can also create your own modules, by extending provided classes (`Module` or `DynamicModule`) or implementing interfaces (`CanProcessCall`, `HasPendingExecution`, `HasInputOutputSchema`).

## Module anatomy

Module consist of 3 important parts:

 - `__construct()` - constructor containing the setup of the module components and dependencies,
 - `signature()` - method returning the signature of the module, which specified expected inputs and resulting output fields,
 - `forward()` - method containing the processing logic, which takes the input data and returns the output data, using the module components configured in the constructor.

## Signatures

Signatures are a way to define the expected inputs and resulting outputs of a module. They are also used to validate the input data and to infer the output data.

Signature of the module returned by `signature()` method can be defined in several ways.

 - you can just return a string in a form of `input1: type, input2: type -> output1: type1, output2: type2`
 - you can return an instance of a class implementing `HasSignature` interface (which has `signature()` method returning the signature string)
 - as an instance of `Signature` class

String based signature is the simplest way to define the signature, but it's less flexible and may be harder to maintain, especially in more complex cases.

`SignatureData` base class is more flexible way to define the inputs and outputs of a module, which can be useful in more complex cases.

Extend `SignatureData` class and define the fields using `#[InputField]` and `#[OutputField]` attributes. The fields can have type hints, which are used to validate the input data. Also, `#[InputField]` and `#[OutputField]` attributes can contain instructions for LLM, specifying the inference behavior.

## Calling the module

Initiation of the module with the input data is done via `withArgs()` or `with()` methods.
- `withArgs()` - takes the input data fields as arguments - they have to be [named arguments](https://stitcher.io/blog/php-8-named-arguments)
- `with()` - takes the input data as an object implementing `HasInputOutputData` interface - can be used if the module has class based signature

`withArgs()` and `with()` methods available on any `Module` class take the input data, and create
`PendingExecution` object, which is executed when you access the results via `result()` or `get()` methods.

## Working with results

Results of the calling the module via `with()` or `withArgs()` is an instance of `PendingExecution` object, containing the ways to access module outputs.

`PendingExecution` object offers several methods to access the output data:

 - `result()` - returns the raw output of the module as defined by `forward()` method,
 - `try()` - returns the output of the module as a `Result` object which is a wrapper around the output data, which can be used to check if the output is valid or if there are any errors before accessing the data, 
 - `get(string $name)` - returns the value of specified output field,
 - `get()` - returns the output data as an array of key-value pairs of field name => field value.

Additionally, `PendingExecution` object offers following methods:

 - `errors()` - returns the list of errors that occurred during the execution of the module,
 - `hasErrors()` - returns `true` if there have been any errors encountered during execution of the module.
