<?php

/**
 * Ok
 *
 * Documentation and API borrowed from Rust: https://doc.rust-lang.org/std/result/enum.Result.html
 *
 * @author Oskar Thornblad
 */

declare(strict_types=1);

namespace App\Domain\OptionalApi\Result;


use Exception;
use App\Domain\OptionalApi\Result;
use PhpOption\{Option, Some, None};


/**
 * Ok
 *
 * @template T
 * The Ok value
 *
 * @template E
 * The Err value
 *
 * @template-extends Result<T, E>
 */
class Ok extends Result
{
    /**
     * @var       mixed
     * @psalm-var T
     */
    private $value;

    /**
     * @var       array
     * @psalm-var list<mixed>
     */
    private $pass;

    /**
     * Ok constructor.
     *
     * @param       mixed $value
     * @psalm-param T $value
     * @param       mixed ...$pass
     */
    public function __construct($value, ...$pass)
    {
        $this->value = $value;
        $this->pass = $pass;
    }

    /**
     * Returns true if the result is Ok.
     *
     * @return true
     */
    public function isOk(): bool
    {
        return true;
    }

    /**
     * Returns true if the result is Err.
     *
     * @return false
     */
    public function isErr(): bool
    {
        return false;
    }

    /**
     * Maps a Result by applying a function to a contained Ok value, leaving an Err value untouched.
     *
     * @template U
     *
     * @param        callable $mapper
     * @psalm-param  callable(T=,mixed...):U $mapper
     * @return       Result
     * @psalm-return Result<U,E>
     */
    public function map(callable $mapper): Result
    {
        return new self($mapper($this->value, ...$this->pass));
    }

    /**
     * Maps a Result by applying a function to a contained Err value, leaving an Ok value untouched.
     *
     * @template F
     *
     * @param        callable $mapper
     * @psalm-param  callable(E=,mixed...):F $mapper
     * @return       Result
     * @psalm-return Result<T,F>
     */
    public function mapErr(callable $mapper): Result
    {
        return new self($this->value, ...$this->pass);
    }

    /**
     * Returns an iterator over the possibly contained value.
     * The iterator yields one value if the result is Ok, otherwise none.
     *
     * @return       array
     * @psalm-return array<int, T>
     */
    public function iter(): array
    {
        return [$this->value];
    }

    /**
     * Returns res if the result is Ok, otherwise returns the Err value of self.
     *
     * @template U
     *
     * @param        Result $res
     * @psalm-param  Result<U,E> $res
     * @return       Result
     * @psalm-return Result<U,E>
     */
    public function and(Result $res): Result
    {
        return $res;
    }

    /**
     * Calls op if the result is Ok, otherwise returns the Err value of self.
     *
     * @template U
     *
     * @param        callable $op
     * @psalm-param  callable(T=,mixed...):Result<U,E> $op
     * @return       Result
     * @psalm-return Result<U,E>
     *
     * @psalm-assert !callable(T=):Result $op
     */
    public function andThen(callable $op): Result
    {
        return $op($this->value, ...$this->pass);
    }

    /**
     * Returns res if the result is Err, otherwise returns the Ok value of self.
     *
     * @template F
     *
     * @param        Result $res
     * @psalm-param  Result<T,F> $res
     * @return       Result
     * @psalm-return Result<T,F>
     */
    public function or(Result $res): Result
    {
        return new self($this->value, ...$this->pass);
    }

    /**
     * Calls op if the result is Err, otherwise returns the Ok value of self.
     *
     * @template F
     *
     * @param        callable $op
     * @psalm-param  callable(E=,mixed...):Result<T,F> $op
     * @return       Result
     * @psalm-return Result<T,F>
     */
    public function orElse(callable $op): Result
    {
        return new self($this->value, ...$this->pass);
    }

    /**
     * Unwraps a result, yielding the content of an Ok. Else, it returns optb.
     *
     * @param        mixed $optb
     * @psalm-param  T $optb
     * @return       mixed
     * @psalm-return T
     */
    public function unwrapOr($optb)
    {
        return $this->value;
    }

    /**
     * Unwraps a result, yielding the content of an Ok. If the value is an Err then it calls op with its value.
     *
     * @param        callable $op
     * @psalm-param  callable(E=,mixed...):T $op
     * @return       mixed
     * @psalm-return T
     */
    public function unwrapOrElse(callable $op)
    {
        return $this->value;
    }

    /**
     * Unwraps a result, yielding the content of an Ok.
     *
     * @return       mixed
     * @psalm-return T
     */
    public function unwrap()
    {
        return $this->value;
    }

    /**
     * Unwraps a result, yielding the content of an Ok.
     *
     * @param        Exception $msg
     * @return       mixed
     * @psalm-return T
     */
    public function expect(Exception $msg)
    {
        return $this->value;
    }

    /**
     * Unwraps a result, yielding the content of an Err.
     *
     * @return       void
     * @psalm-return never-return
     * @throws       ResultException if the value is an Ok.
     */
    public function unwrapErr()
    {
        throw new ResultException('Unwrapped with the expectation of Err, but found Ok');
    }

    /**
     * Applies values inside the given Results to the function in this Result.
     *
     * @param        Result ...$inArgs Results to apply the function to.
     * @return       Result
     * @psalm-return Result<mixed,E>
     *
     * @throws ResultException
     */
    public function apply(Result ...$inArgs): Result
    {
        if (!is_callable($this->value)) {
            throw new ResultException('Tried to apply a non-callable to arguments');
        }

        return array_reduce(
            $inArgs, function (Result $final, Result $argResult): Result {
                return $final->andThen(
                    function (array $outArgs) use ($argResult): Result {
                        return $argResult->map(
                            function ($unwrappedArg) use ($outArgs): array {
                                $outArgs[] = $unwrappedArg;
                                return $outArgs;
                            }
                        );
                    }
                );
            }, new self([])
        )
            ->map(
                /**
                 * @return mixed
                 */
                function (array $argArray) {
                    return call_user_func_array($this->value, $argArray);
                }
            );
    }

    /**
     * Converts from Result<T, E> to Option<T>, and discarding the error, if any
     *
     * @return       Option
     * @psalm-return Option<T>
     */
    public function ok(): Option
    {
        return new Some($this->value);
    }

    /**
     * Converts from Result<T, E> to Option<E>, and discarding the value, if any
     *
     * @return       Option
     * @psalm-return Option<E>
     */
    public function err(): Option
    {
        return new None;
    }

    /**
     * The attached pass-through args will be unpacked into extra args into chained callables
     *
     * @param        mixed ...$args
     * @return       Result
     * @psalm-return Result<T,E>
     */
    public function with(...$args): Result
    {
        $this->pass = $args;

        return $this;
    }
}