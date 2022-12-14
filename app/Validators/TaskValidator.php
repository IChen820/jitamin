<?php

/*
 * This file is part of Jitamin.
 *
 * Copyright (C) Jitamin Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jitamin\Validator;

use SimpleValidator\Validator;
use SimpleValidator\Validators;

/**
 * Task Validator.
 */
class TaskValidator extends BaseValidator
{
    /**
     * Common validation rules.
     *
     * @return array
     */
    private function commonValidationRules()
    {
        return [
            new Validators\Integer('id', t('This value must be an integer')),
            new Validators\Integer('project_id', t('This value must be an integer')),
            new Validators\Integer('column_id', t('This value must be an integer')),
            new Validators\Integer('owner_id', t('This value must be an integer')),
            new Validators\Integer('creator_id', t('This value must be an integer')),
            new Validators\Integer('score', t('This value must be an integer')),
            new Validators\Range('score', t('This value must be in the range %d to %d', -2147483647, 2147483647), -2147483647, 2147483647),
            new Validators\Integer('category_id', t('This value must be an integer')),
            new Validators\Integer('swimlane_id', t('This value must be an integer')),
            new Validators\Integer('recurrence_child', t('This value must be an integer')),
            new Validators\Integer('recurrence_parent', t('This value must be an integer')),
            new Validators\Integer('recurrence_factor', t('This value must be an integer')),
            new Validators\Integer('recurrence_timeframe', t('This value must be an integer')),
            new Validators\Integer('recurrence_basedate', t('This value must be an integer')),
            new Validators\Integer('recurrence_trigger', t('This value must be an integer')),
            new Validators\Integer('recurrence_status', t('This value must be an integer')),
            new Validators\Integer('priority', t('This value must be an integer')),
            new Validators\MaxLength('title', t('The maximum length is %d characters', 200), 200),
            new Validators\MaxLength('reference', t('The maximum length is %d characters', 50), 50),
            new Validators\Date('date_due', t('Invalid date'), $this->dateParser->getParserFormats()),
            new Validators\Date('date_started', t('Invalid date'), $this->dateParser->getParserFormats()),
            new Validators\Numeric('time_spent', t('This value must be numeric')),
            new Validators\Numeric('time_estimated', t('This value must be numeric')),
        ];
    }

    /**
     * Validate task creation.
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateCreation(array $values)
    {
        $rules = [
            new Validators\Required('project_id', t('The project is required')),
            new Validators\Required('title', t('The title is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate task creation.
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateBulkCreation(array $values)
    {
        $rules = [
            new Validators\Required('project_id', t('The project is required')),
            new Validators\Required('tasks', t('Field required')),
            new Validators\Required('column_id', t('Field required')),
            new Validators\Required('swimlane_id', t('Field required')),
            new Validators\Integer('category_id', t('This value must be an integer')),
            new Validators\Integer('swimlane_id', t('This value must be an integer')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate edit recurrence.
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateEditRecurrence(array $values)
    {
        $rules = [
            new Validators\Required('id', t('The id is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate task modification (form).
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateModification(array $values)
    {
        $rules = [
            new Validators\Required('id', t('The id is required')),
            new Validators\Required('title', t('The title is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate task modification (Api).
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateApiModification(array $values)
    {
        $rules = [
            new Validators\Required('id', t('The id is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate project modification.
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateProjectModification(array $values)
    {
        $rules = [
            new Validators\Required('id', t('The id is required')),
            new Validators\Required('project_id', t('The project is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }

    /**
     * Validate time tracking modification (form).
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateTimeModification(array $values)
    {
        $rules = [
            new Validators\Required('id', t('The id is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }
}
