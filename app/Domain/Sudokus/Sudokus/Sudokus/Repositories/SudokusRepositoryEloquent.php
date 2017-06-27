<?php

namespace sudoku\Domain\Sudokus\Sudokus\Repositories;

use sudoku\Domain\Sudokus\Puzzles\Repositories\Puzzle;
use sudoku\Infrastructure\Contracts\
{
	Repositories\RepositoryEloquentAbstract,
	Request\RequestAbstract
};
use sudoku\Domain\Sudokus\Sudokus\
{
	Sudoku,
	Repositories\SudokusRepositoryInterface
};

class SudokusRepositoryEloquent extends RepositoryEloquentAbstract implements SudokusRepositoryInterface
{

	/**
	 * Specify Model class name
	 *
	 * @return string
	 */
	public function model() {
		return Sudoku::class;
	}

	/**
	 * Create sudoku.
	 *
	 * @param array $attributes
	 *
	 * @return \sudoku\Domain\Sudokus\Sudokus\Sudoku
	 */
	public function create(array $attributes) {
		$sudoku = parent::create($attributes);

		return $sudoku;
	}

	/**
	 * Update sudoku.
	 *
	 * @param array   $attributes
	 * @param integer $sudoku_id
	 *
	 * @return \sudoku\Domain\Sudokus\Sudokus\Sudoku
	 */
	public function update(array $attributes, $sudoku_id) {
		$sudoku = parent::update($attributes, $sudoku_id);

		return $sudoku;
	}

	/**
	 * Delete sudoku.
	 *
	 * @param array   $attributes
	 * @param integer $sudoku_id
	 *
	 * @return \sudoku\Domain\Sudokus\Sudokus\Sudoku
	 */
	public function delete($sudoku_id) {
		$sudoku = $this->find($sudoku_id);

		parent::delete($sudoku->id);

		return $sudoku;
	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function frontendIndexDisplaySudokuView(RequestAbstract $request) {

		// Puzzle size
		$cellSize = 25;

		if ($request->has('cell_size'))
		{
			$cellSize = (int) $request->get('cell_size');
		}

		$puzzle = $this->generatePuzzleFromConstraints($cellSize);

		/*
		 * Register puzzle
		 */

		$sudoku = $this->create([
			'user_id'  => \Auth::user()->id,
			'puzzle'   => $puzzle->getPuzzleAsJson(),
			'solution' => $puzzle->getSolutionAsJson(),
		]);

		return view(
			'frontend.sudoku.index',
			[
				'thePuzzle'        => $sudoku->puzzle_collection,
				'theSolution'      => $sudoku->solution_collection,
				'isSolvable'       => $puzzle->isSolvable(),
				'selectedCellSize' => $cellSize,
			]
		);
	}

	/**
	 * @param $cellSize
	 *
	 * @return Puzzle
	 */
	protected function generatePuzzleFromConstraints($cellSize) {
		// Is the puzzle solvable ?
		$isSolvable = false;

		// New puzzle
		$puzzle = new Puzzle();

		// Execute until a resolvable puzzle is generated
		do
		{

			// Create new puzzle
			$puzzle->generatePuzzle($cellSize);

			// Get the solution and check it
			$isSolvable = $puzzle->isSolvable();

		} while (false === $isSolvable);

		$puzzle->solve();

		return $puzzle;
	}
}
