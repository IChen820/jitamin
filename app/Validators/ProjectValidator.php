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

use Jitamin\Model\ProjectModel;
use SimpleValidator\Validator;
use SimpleValidator\Validators;

/**
 * Project Validator.
 */
class ProjectValidator extends BaseValidator
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
            new Validators\Integer('priority_default', t('This value must be an integer')),
            new Validators\Integer('priority_start', t('This value must be an integer')),
            new Validators\Integer('priority_end', t('This value must be an integer')),
            new Validators\Integer('is_active', t('This value must be an integer')),
            new Validators\NotEmpty('name', t('This field cannot be empty')),
            new Validators\MaxLength('name', t('The maximum length is %d characters', 50), 50),
            new Validators\MaxLength('identifier', t('The maximum length is %d characters', 50), 50),
            new Validators\MaxLength('start_date', t('The maximum length is %d characters', 10), 10),
            new Validators\MaxLength('end_date', t('The maximum length is %d characters', 10), 10),
            new Validators\AlphaNumeric('identifier', t('This value must be alphanumeric')),
            new Validators\Unique('identifier', t('The identifier must be unique'), $this->db->getConnection(), ProjectModel::TABLE),
        ];
    }

    /**
     * Validate project creation.
     *
     * @param array $values Form values
     *
     * @return array $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateCreation(array $values)
    {
        $rules = [
            new Validators\Required('name', t('The project name is required')),
        ];

        if (!empty($values['identifier'])) {
            $values['identifier'] = strtoupper($values['identifier']);
        }

        $v = new Validator($values, array_merge($this->commonValidationRules(), $rules));

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
    public function validateModification(array $values)
    {
        if (!empty($values['identifier'])) {
            $values['identifier'] = strtoupper($values['identifier']);
        }

        $rules = [
            new Validators\Required('id', t('This value is required')),
        ];

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return [
            $v->execute(),
            $v->getErrors(),
        ];
    }
}
