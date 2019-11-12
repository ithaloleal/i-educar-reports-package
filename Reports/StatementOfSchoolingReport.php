<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StatementOfSchoolingReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @var string
     */
    private $modelo;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'statement-of-schooling';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('matricula');
//        $this->addRequiredArg('modelo');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $escola = $this->args['escola'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "
      select 
      coalesce(instituicao.altera_atestado_para_declaracao, false) AS altera_atestado_para_declaracao,
      cod_matricula,
      cod_escola,
       pessoa_escola.nome as escola,
       nm_serie,
       nm_curso,
       cod_aluno_inep as cod_inep,
       aluno.cod_aluno,
       pessoa_aluno.nome as aluno,
       data_nasc,
       ano,
       aprovado,
       pessoa_pai.nome as nm_pai,
       pessoa_mae.nome as nm_mae,
       (SELECT CASE WHEN matricula.aprovado = 1 THEN 'Aprovado'
                   WHEN matricula.aprovado = 2 THEN 'Reprovado'
                   WHEN matricula.aprovado = 3 THEN 'Em Andamento'
                   WHEN matricula.aprovado = 4 THEN 'Transferido'
                   WHEN matricula.aprovado = 6 THEN 'Abandono'
                   WHEN matricula.aprovado = 32 THEN 'Desistente'
          END) as situacao,
          diretor.nome as diretor,
          secretario.nome as secretario,
          instituicao.cidade
      from matricula
      left outer join escola on matricula.ref_ref_cod_escola = escola.cod_escola
      left outer join cadastro.pessoa pessoa_escola ON (pessoa_escola.idpes = escola.ref_idpes)
      left outer join cadastro.pessoa diretor on escola.ref_idpes_gestor = diretor.idpes
      left outer join cadastro.pessoa secretario ON secretario.idpes = escola.ref_idpes_secretario_escolar
      left outer join instituicao on escola.ref_cod_instituicao = instituicao.cod_instituicao
      left outer join pmieducar.serie on matricula.ref_ref_cod_serie = serie.cod_serie
      left outer join pmieducar.curso on serie.ref_cod_curso = curso.cod_curso
      left outer join pmieducar.aluno on matricula.ref_cod_aluno = aluno.cod_aluno
      left outer join cadastro.pessoa pessoa_aluno on aluno.ref_idpes = pessoa_aluno.idpes
      left outer join cadastro.fisica pessoa_fisica_aluno on aluno.ref_idpes = pessoa_fisica_aluno.idpes
      left outer join cadastro.pessoa pessoa_pai on pessoa_fisica_aluno.idpes_pai = pessoa_pai.idpes
      left outer join cadastro.pessoa pessoa_mae on pessoa_fisica_aluno.idpes_mae = pessoa_mae.idpes
      left outer join modules.educacenso_cod_aluno on educacenso_cod_aluno.cod_aluno = aluno.cod_aluno
       where cod_matricula = {$matricula}";
    }
}
