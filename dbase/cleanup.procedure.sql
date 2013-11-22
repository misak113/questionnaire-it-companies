CREATE PROCEDURE `cleanup` ()
BEGIN

DELETE company FROM company
LEFT JOIN questionnaire USING (company_id)
WHERE questionnaire_id IS NULL;

DELETE person FROM person
LEFT JOIN questionnaire ON (
 questionnaire.manager_person_id = person.person_id
 OR questionnaire.developer_person_id = person.person_id
)
WHERE questionnaire_id IS NULL;

DELETE position FROM position
LEFT JOIN questionnaire ON (
 questionnaire.work_position_id = position.position_id
)
LEFT JOIN person ON (
 person.position_id = position.position_id
)
WHERE questionnaire_id IS NULL AND person_id IS NULL;

END;; -- 0.003 s