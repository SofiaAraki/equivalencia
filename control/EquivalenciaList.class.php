<?php

use Adianti\Database\TTransaction;
use Adianti\Widget\Form\TEntry;

class EquivalenciaList extends TStandardList
{
    protected $form;     
    protected $datagrid; 
    protected $pageNavigation;

    use Adianti\base\AdiantiStandardListTrait;


    public function __construct()
    {
        parent::__construct();
        parent::setDatabase('dados_fei');           // Define o banco de dados
        parent::setActiveRecord('VwHistoricodisciplina');    // Define a Active Record // tabela do banco de dados
        parent::setLimit(50);                       // Define o limite de registros por página

        // Define os filtros
        // Ele permite que o campo do formulário seja vinculado a uma coluna específica da tabela no banco de dados
        //$this->addFilterField('campoTabela', 'operador', 'campoFormulario');   
        $this->addFilterField('Codaluno', '=', 'Codaluno'); // filterField, operator, formField        

        // Criação do formulário de busca
        $this->form = new BootstrapFormBuilder('form_search_Equivalencia');
        $this->form->setFormTitle('Equivalencia de Disciplina'); //titulo do formulário

        // Campos do formulário
        //TEntry é um campo de entrada de texto que permite que o usuário digite um valor
        $Codaluno = new TEntry('Codaluno');
        $NomeAluno = new TEntry('Nome');
        $NomeCurso = new TEntry('NomeCurso');

        $this->form->addFields( 
            [ new TLabel('Codaluno'), $Codaluno ], 
            [ new TLabel('Nome'), $NomeAluno ],
            [ new TLabel('Nomecurso'), $NomeCurso ] 
        ) ->layout = ['col-sm-1', 'col-sm-2', 'col-sm-2'];

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';  

        // Criação da DataGrid // exibir dados em formato de tabela 
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        // Define as colunas da DataGrid
        //$column = new TDataGridColumn('atributo', 'rótulo', 'alinhamento', 'largura');
        $column_Id = new TDataGridColumn('Id_Eq', 'ID', 'left', '5%');
        $column_Disciplina = new TDataGridColumn('NomeDisciplina', 'Disciplina', 'center', '20%');
        $column_Instituicao = new TDataGridColumn('Instituicao', 'Instituicao', 'center', '10%');
        $column_Equivalencia = new TDataGridColumn('Equivalencia', 'Equivalencia', 'center', '20%');
        $column_Nota = new TDataGridColumn('Nota', 'Nota', 'center', '10%');
        $column_CH = new TDataGridColumn('CH', 'Ch', 'center', '10%');
        $column_Ano = new TDataGridColumn('Ano', 'Ano', 'center', '10%');
        $column_Semestre = new TDataGridColumn('Semestre', 'Semestre', 'center', '10%');

        // adiciona as colunas na DataGrid
        //$this->datagrid->addColumn($column_Id); // Certifique-se de adicionar essa coluna
        $this->datagrid->addColumn($column_Disciplina);
        $this->datagrid->addColumn($column_Instituicao);
        $this->datagrid->addColumn($column_Equivalencia);
        $this->datagrid->addColumn($column_Nota);
        $this->datagrid->addColumn($column_CH);
        $this->datagrid->addColumn($column_Ano);
        $this->datagrid->addColumn($column_Semestre);

        // edição na linha
        $this->createEditAction('Instituicao', $column_Instituicao);
        $this->createEditAction('Equivalencia', $column_Equivalencia);
        $this->createEditAction('Nota', $column_Nota);
        $this->createEditAction('CH', $column_CH);
        $this->createEditAction('Ano', $column_Ano);
        $this->createEditAction('Semestre', $column_Semestre);

        // Criação do modelo da DataGrid
        $this->datagrid->createModel();

        // Criação da navegação de página // configura a exibição dos registos 
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // Criação do painel 
        $panel = new TPanelGroup('', 'white'); //sem titulo e cor branca
        $panel->add($this->datagrid); // adiciona a datagrid ao painel, que são as colunas
        $panel->addFooter($this->pageNavigation); // adiciona a navegação de página ao final da pagina - footer

        // Criação do container // TVBox é um container que permite organizar os elementos em uma coluna vertical
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($panel);

        // Adiciona o container à página
        parent::add($container);

    }

    
    private function createEditAction($columnName, $column) {
        $editAction = new TDataGridAction([$this, 'onSaveInLine']);
        $editAction->setField('Id_Eq'); // A coluna deve ser referenciada corretamente
    
        // Certifique-se de que está passando o Codaluno
        $codaluno = $this->form->getData()->Codaluno;
        if ($codaluno === 'undefined') {
            $codaluno = null;
        }

    
        // Passa Codaluno como um inteiro
        $editAction->setParameter('Codaluno', (int)$codaluno);
        $editAction->setParameter('Id_Eq', '{Id_Eq}'); // Passa o Id_Eq do registro sendo editado
        $editAction->setParameter('column', $columnName);
        $column->setEditAction($editAction);
    }
    
    
    public function onSaveInLine($param) {
        try {
            TTransaction::open('dados_fei'); 
    
            // Se Id_Eq não estiver definido, cria um novo registro
            if (empty($param['Id_Eq']) || $param['Id_Eq'] === 'undefined') {
                // Cria uma nova instância de Equivalencia
                $newEquivalencia = new Equivalencia(); // Não atribuindo Id_Eq
    
                // Atribuindo valores para os novos registros
                $newEquivalencia->Codaluno = (int)$param['Codaluno']; // Atribuindo Codaluno
                $newEquivalencia->Instituicao = $param['Instituicao'] ?? null;
                $newEquivalencia->Equivalencia = $param['Equivalencia'] ?? null;
                $newEquivalencia->Nota = $param['Nota'] ?? null;
                $newEquivalencia->CH = $param['CH'] ?? null;
                $newEquivalencia->Ano = $param['Ano'] ?? null;
                $newEquivalencia->Semestre = $param['Semestre'] ?? null;
    
                // O ID será gerado automaticamente
                $newEquivalencia->store();
    
                // Verifica se o ID foi gerado
                if (!$newEquivalencia->Id_Eq) {
                    throw new Exception("O ID não foi gerado após a inserção do registro.");
                }
            } else {
                // Se Id_Eq estiver definido, você está atualizando um registro existente
                $newEquivalencia = new Equivalencia($param['Id_Eq']); // Atribuindo a instância existente
                
                // Log para debugar o Id_Eq
                error_log('Atualizando registro com Id_Eq: ' . $param['Id_Eq']);
    
                // Verifica se a instância foi carregada
                if (!$newEquivalencia->Id_Eq) {
                    throw new Exception("Registro não encontrado para o ID: " . $param['Id_Eq']);
                }
    
                // Atribuindo valores para o registro existente
                $newEquivalencia->Instituicao = $param['Instituicao'] ?? null;
                $newEquivalencia->Equivalencia = $param['Equivalencia'] ?? null;
                $newEquivalencia->Nota = $param['Nota'] ?? null;
                $newEquivalencia->CH = $param['CH'] ?? null;
                $newEquivalencia->Ano = $param['Ano'] ?? null;
                $newEquivalencia->Semestre = $param['Semestre'] ?? null;
    
                // Armazenando o registro atualizado
                $newEquivalencia->store();
            }
    
            TTransaction::close(); 
            $this->onReload(); 
        } catch (Exception $e) {
            TTransaction::rollback(); 
            // Log do erro para depuração
            error_log('Erro ao salvar em linha: ' . $e->getMessage());
            new TMessage('error', $e->getMessage()); 
        }
    }       


    public function onReload($param = null) {
        try {
            TTransaction::open('dados_fei'); 
    
            $this->form->validate();
            $data = $this->form->getData();
            
            // Verifica se o campo Codaluno está preenchido antes de continuar
            if (!empty($data->Codaluno)) {
                // Buscar o nome do aluno
                $aluno = FiAluno::find($data->Codaluno);
                if (!$aluno) {
                    new TMessage('error', "Aluno não encontrado.");
                    $data->Codaluno = ''; // Reseta o Codaluno se não encontrado
                    $this->form->setData($data);
                    return;
                }
    
                $data->Nome = $aluno->Nome;
    
                // Buscar o curso e disciplinas relacionadas ao código do aluno
                $cursoRepository = new TRepository('VwHistoricodisciplina');
                $cursoCriteria = new TCriteria;
                $cursoCriteria->add(new TFilter('Codaluno', '=', $data->Codaluno));
                $curso = $cursoRepository->load($cursoCriteria);
    
                // Verifique se o curso foi encontrado
                if (!$curso) {
                    new TMessage('error', "Curso não encontrado.");
                    $data->Codaluno = ''; // Reseta o Codaluno se curso não encontrado
                    $this->form->setData($data);
                    return;
                }
    
                $data->NomeCurso = $curso[0]->NomeCurso; // Usando o primeiro registro
                $this->form->setData($data);
    
                // Limpa a DataGrid
                $this->datagrid->clear();
    
                // Preencher a DataGrid com disciplinas e equivalências
                foreach ($curso as $disciplina) {
                    $item = new stdClass();
                    $item->Id_Eq = null; // Inicializa como nulo
    
                    $item->NomeDisciplina = $disciplina->NomeDisciplina;
    
                    // Buscar equivalências para a disciplina
                    $equivalenciaRepository = new TRepository('Equivalencia');
                    $equivalenciaCriteria = new TCriteria;
                    $equivalenciaCriteria->add(new TFilter('Codaluno', '=', $data->Codaluno));
                    $equivalencias = $equivalenciaRepository->load($equivalenciaCriteria);
    
                    // Inicializa campos
                    $item->Instituicao = '';
                    $item->Equivalencia = '';
                    $item->Nota = '';
                    $item->CH = '';
                    $item->Ano = '';
                    $item->Semestre = '';
    
                    // Se houver equivalências, preencha os campos correspondentes
                    if ($equivalencias) {
                        foreach ($equivalencias as $equivalencia) {
                            // Lógica para mapear as equivalências à disciplina
                            if ($equivalencia->NomeDisciplina === $item->NomeDisciplina) {
                                $item->Id_Eq = $equivalencia->Id_Eq; // Atribui o Id_Eq se houver correspondência
                                $item->Instituicao = $equivalencia->Instituicao;
                                $item->Equivalencia = $equivalencia->Equivalencia;
                                $item->Nota = $equivalencia->Nota;
                                $item->CH = $equivalencia->CH;
                                $item->Ano = $equivalencia->Ano;
                                $item->Semestre = $equivalencia->Semestre;
                            }
                        }
                    }
    
                    // Adicionar item à DataGrid
                    $this->datagrid->addItem($item);
                }
            } else {
                // Se Codaluno estiver vazio, limpa todos os dados da form
                $data->Codaluno = '';
                $data->Nome = '';
                $data->NomeCurso = '';
                $this->form->setData($data);
                $this->datagrid->clear();
            }
    
            TTransaction::close(); 
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
     
}