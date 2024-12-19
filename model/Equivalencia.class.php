<?php
class Equivalencia extends TRecord
{
    const TABLENAME = 'Equivalencia';
    const PRIMARYKEY = 'Id_Eq';
    const IDPOLICY = 'max';

    private $vw_historicodisciplina;
    private $Fi_aluno;

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('Instituicao');
        parent::addAttribute('Equivalencia');
        parent::addAttribute('Nota');
        parent::addAttribute('CH');
        parent::addAttribute('Ano');
        parent::addAttribute('Semestre');  
        parent::addAttribute('Codaluno'); // FK - FI_Aluno
    }

    /**
     * Method set_vw_historicodisciplina
     * Sample of usage: $equivalencia->vw_historicodisciplina = $object;
     * @param $object Instance of VwHistoricodisciplina
     */
    public function set_vw_historicodisciplina(VwHistoricodisciplina $object)
    {
        $this->vw_historicodisciplina = $object;
        $this->Codaluno = $object->id;
    }
    
    /**
     * Method get_vw_historicodisciplina
     * Sample of usage: $equivalencia->vw_historicodisciplina->attribute;
     * @returns VwHistoricodisciplina instance
     */
    public function get_vw_historicodisciplina()
    {
        if (empty($this->vw_historicodisciplina))
            $this->vw_historicodisciplina = new VwHistoricodisciplina($this->Codaluno);
    
        return $this->vw_historicodisciplina;
    }

    public function set_Fi_aluno(FiAluno $object)
    {
        $this->Fi_aluno = $object;
        $this->Codaluno = $object->id;
    }

    public function get_Fi_aluno()
    {
        if (empty($this->Fi_aluno))
            $this->Fi_aluno = new FiAluno($this->Codaluno);

        return $this->Fi_aluno;
    }

}
