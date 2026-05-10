# SGFE — Assets institucionais

Data da pesquisa: 2026-05-09

## Fontes consultadas

- Portal Oficial do Governo de Angola — Símbolos Nacionais: https://governo.gov.ao/angola/simbolos-nacionais
- Portal Oficial do Governo de Angola — Termos e Políticas: https://governo.gov.ao/termos
- MINFIN — Portal Ministério das Finanças de Angola: https://www.minfin.gov.ao/
- Portal do Contribuinte/MINFIN: https://portaldocontribuinte.minfin.gov.ao/
- Wikimedia Commons — Emblem of Angola.svg, como fonte pública de referência quando necessário: https://commons.wikimedia.org/wiki/File:Emblem_of_Angola.svg

## Decisão aplicada

O frontend usa apenas o asset local `frontend/public/assets/insignia-republica-angola.png`, copiado de `backend/public/images/brasao-angola.png`, sem alteração de proporção, cores ou composição.

Não foi usado o ficheiro `backend/public/images/brasao-angola.svg`, porque o conteúdo contém texto residual de erro e não deve ser tratado como asset oficial.

Não foi usado o ficheiro `backend/public/images/minfin-logo.svg` como marca oficial, porque o conteúdo aparenta ser uma composição simplificada/local e não foi possível confirmar uma fonte oficial em formato adequado durante a pesquisa. A interface identifica o órgão em texto: “República de Angola” e “Ministério das Finanças”.

## Nota de uso

Os termos do Portal Oficial do Governo de Angola indicam que o uso da logomarca do Portal do Governo e do Brasão/Insígnia da República é exclusivo do Governo da República de Angola e seus organismos. Como o SGFE é especificado como sistema estatal/institucional, o asset foi mantido para contexto governamental. Em distribuição fora de órgão oficial, validar autorização formal antes de publicar.
