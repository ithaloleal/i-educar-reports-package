<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class VacancySwingReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'vacancy-swing';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select 
f.nome as escola, 
nm_serie, 
sum(max_aluno) as max_vagas,
sum(total_alunos) as total_alunos,
(sum(max_aluno) - sum(total_alunos)) as restantes,
trunc((sum(total_alunos) / sum(max_aluno) * 100), 2) as percentual
from pmieducar.turma 
left outer join pmieducar.serie ON serie.cod_serie = turma.ref_ref_cod_serie
left outer join pmieducar.escola ON escola.cod_escola = turma.ref_ref_cod_escola
left outer join cadastro.pessoa f on escola.ref_idpes = f.idpes
left outer join (
	select 
		cod_turma,
		count(*) as total_alunos
	from matricula m
	left outer join matricula_turma mt on mt.ref_cod_matricula = m.cod_matricula
	left outer join pmieducar.turma t ON mt.ref_cod_turma = t.cod_turma
	left outer join pmieducar.serie s ON s.cod_serie = t.ref_ref_cod_serie
	left outer join pmieducar.escola e ON e.cod_escola = t.ref_ref_cod_escola
	left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
	where m.ativo = 1
	group by 1
) as tmp on tmp.cod_turma = turma.cod_turma
where turma.ano = {$ano}
        and (CASE WHEN  0 = {$escola} THEN true ELSE {$escola} = escola.cod_escola END)
	    and (CASE WHEN  0 = {$curso} THEN true ELSE {$curso} = serie.ref_cod_curso END)
	    and (CASE WHEN  0 = {$serie} THEN true ELSE {$serie} = serie.cod_serie END)
	    and (CASE WHEN  0 = {$turma} THEN true ELSE {$turma} = turma.cod_turma END)
group by 1,2
	    HAVING (CASE WHEN  9 = {$situacao} THEN true WHEN {$situacao} = 1 THEN sum(total_alunos) > sum(max_aluno) ELSE sum(total_alunos) < sum(max_aluno) END)
order by 1,2";
    }
}
