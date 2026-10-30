<?php

namespace App\DAO;

use FW\DB\Connection;

/**
 * Public site content managed in System settings and shown on /research.
 *
 * Every list section is described once in SECTIONS; table and column names
 * only ever come from here, never from the request.
 */
class SiteContentDAO extends Connection
{
    const SECTIONS = [
        'institutions' => [
            'label' => 'Institutions',
            'item' => 'institution',
            'table' => 'site_institution',
            'pk' => 'ins_id',
            'order' => 'ins_order',
            // Listed and reordered within each type, universities first (as on /research)
            'group' => ['col' => 'ins_type', 'sequence' => ['university', 'department', 'other']],
            'images' => ['ins_logo' => 'Logo'],
            'fields' => [
                'ins_type' => ['label' => 'Type', 'type' => 'select', 'required' => true,
                               'options' => ['department' => 'Department', 'university' => 'University', 'other' => 'Other entity']],
                'ins_name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 200],
                'ins_acronym' => ['label' => 'Acronym', 'type' => 'text', 'max' => 40],
                'ins_url' => ['label' => 'Website', 'type' => 'url', 'max' => 255],
            ],
        ],
        'people' => [
            'label' => 'People',
            'item' => 'person',
            'table' => 'site_person',
            'pk' => 'per_id',
            'order' => 'per_order',
            // Researchers and supervisors are ordered separately, as /research shows them
            'group' => ['col' => 'per_role', 'sequence' => ['researcher', 'supervisor']],
            'images' => ['per_photo' => 'Photo'],
            'fields' => [
                'per_role' => ['label' => 'Role', 'type' => 'select', 'required' => true, 'group' => 'Participant',
                               'options' => ['researcher' => 'Researcher', 'supervisor' => 'Supervisor']],
                'per_name' => ['label' => 'Name', 'type' => 'text', 'required' => true, 'max' => 150, 'group' => 'Participant'],
                'per_title' => ['label' => 'Title or position', 'type' => 'text', 'max' => 150, 'group' => 'Participant', 'placeholder' => 'e.g. Prof. Dr., PhD candidate'],
                'fk_site_institution_ins_id' => ['label' => 'Entity', 'type' => 'entity', 'group' => 'Participant',
                               'hint' => 'Its logo is shown with the participant. Add entities in the Institutions tab.'],
                'per_email' => ['label' => 'E-mail', 'type' => 'email', 'required' => true, 'max' => 150, 'group' => 'Contact'],
                'per_phone' => ['label' => 'Phone', 'type' => 'text', 'max' => 40, 'group' => 'Contact', 'placeholder' => 'Optional'],
                'per_lattes' => ['label' => 'Lattes', 'type' => 'url', 'max' => 255, 'group' => 'Academic profiles', 'placeholder' => 'http://lattes.cnpq.br/…'],
                'per_orcid' => ['label' => 'ORCID', 'type' => 'url', 'max' => 255, 'group' => 'Academic profiles', 'placeholder' => 'https://orcid.org/0000-0000-0000-0000'],
                'per_researchgate' => ['label' => 'ResearchGate', 'type' => 'url', 'max' => 255, 'group' => 'Social links'],
                'per_linkedin' => ['label' => 'LinkedIn', 'type' => 'url', 'max' => 255, 'group' => 'Social links'],
                'per_instagram' => ['label' => 'Instagram', 'type' => 'url', 'max' => 255, 'group' => 'Social links'],
                'per_website' => ['label' => 'Website', 'type' => 'url', 'max' => 255, 'group' => 'Social links'],
            ],
        ],
        'publications' => [
            'label' => 'Publications',
            'item' => 'publication',
            'table' => 'site_publication',
            'pk' => 'pub_id',
            'order' => 'pub_order',
            'fields' => [
                'pub_title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 300],
                'pub_authors' => ['label' => 'Authors', 'type' => 'text', 'max' => 500],
                'pub_venue' => ['label' => 'Journal, conference or thesis', 'type' => 'text', 'max' => 200],
                'pub_year' => ['label' => 'Year', 'type' => 'year'],
                'pub_doi' => ['label' => 'DOI', 'type' => 'text', 'max' => 120, 'placeholder' => '10.xxxx/xxxxx'],
                'pub_url' => ['label' => 'Link', 'type' => 'url', 'max' => 255],
            ],
        ],
        'projects' => [
            'label' => 'Projects',
            'item' => 'project',
            'table' => 'site_project',
            'pk' => 'pro_id',
            'order' => 'pro_order',
            'fields' => [
                'pro_title' => ['label' => 'Title', 'type' => 'text', 'required' => true, 'max' => 300],
                'pro_period' => ['label' => 'Period', 'type' => 'text', 'max' => 60, 'placeholder' => 'e.g. 2023–2027'],
                'pro_description' => ['label' => 'Description', 'type' => 'textarea'],
                'pro_url' => ['label' => 'Link', 'type' => 'url', 'max' => 255],
            ],
        ],
        'funders' => [
            'label' => 'Funding',
            'item' => 'funder',
            'table' => 'site_funder',
            'pk' => 'fun_id',
            'order' => 'fun_order',
            'images' => ['fun_logo' => 'Logo'],
            'fields' => [
                'fun_name' => ['label' => 'Funding agency', 'type' => 'text', 'required' => true, 'max' => 200],
                'fun_grant' => ['label' => 'Grant or process number', 'type' => 'text', 'max' => 120],
                'fun_url' => ['label' => 'Website', 'type' => 'url', 'max' => 255],
            ],
        ],
    ];

    // Free texts, stored in site_setting
    const TEXTS = [
        'motivation' => 'Motivation',
        'acknowledgements' => 'Acknowledgements',
    ];

    public static function section($key)
    {
        return self::SECTIONS[$key] ?? null;
    }

    public function all($key)
    {
        $s = self::section($key);
        $sql = "SELECT * FROM {$s['table']} ORDER BY " . self::groupOrder($s) . "{$s['order']}, {$s['pk']}";
        return $this->getConn()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ORDER BY prefix that keeps a grouped section's groups in their fixed sequence
    private static function groupOrder($s)
    {
        if (empty($s['group'])) {
            return '';
        }
        $seq = implode(', ', array_map(function ($v) { return "'" . $v . "'"; }, $s['group']['sequence']));
        return "FIELD({$s['group']['col']}, $seq), ";
    }

    public function find($key, $id)
    {
        $s = self::section($key);
        $stmt = $this->getConn()->prepare("SELECT * FROM {$s['table']} WHERE {$s['pk']} = :id");
        $stmt->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    // Insert when $id is null, update otherwise. $data keys are already whitelisted.
    public function save($key, $id, array $data)
    {
        $s = self::section($key);
        $cols = array_keys($data);
        if ($id === null) {
            // New items go to the end of the list (of their group, where there is one)
            $where = '';
            if (!empty($s['group']) && isset($data[$s['group']['col']])) {
                $where = " WHERE {$s['group']['col']} = " . $this->getConn()->quote($data[$s['group']['col']]);
            }
            $data[$s['order']] = 1 + (int) $this->getConn()->query("SELECT COALESCE(MAX({$s['order']}), 0) FROM {$s['table']}$where")->fetchColumn();
            $cols = array_keys($data);
            $sql = "INSERT INTO {$s['table']} (" . implode(', ', $cols) . ") VALUES (:" . implode(', :', $cols) . ")";
        } else {
            $sql = "UPDATE {$s['table']} SET " . implode(', ', array_map(function ($c) { return "$c = :$c"; }, $cols))
                 . " WHERE {$s['pk']} = :pk";
        }
        $stmt = $this->getConn()->prepare($sql);
        foreach ($data as $c => $v) {
            $stmt->bindValue(':' . $c, $v === '' ? null : $v);
        }
        if ($id !== null) {
            $stmt->bindValue(':pk', (int) $id, \PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public function delete($key, $id)
    {
        $s = self::section($key);
        $stmt = $this->getConn()->prepare("DELETE FROM {$s['table']} WHERE {$s['pk']} = :id");
        $stmt->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Swap an item with its neighbour above (-1) or below (+1)
    public function move($key, $id, $dir)
    {
        $s = self::section($key);
        $rows = $this->all($key);
        // In a grouped section an item only moves among the items of its own group
        if (!empty($s['group'])) {
            $col = $s['group']['col'];
            $own = null;
            foreach ($rows as $r) {
                if ((int) $r[$s['pk']] === (int) $id) $own = $r[$col];
            }
            $rows = array_values(array_filter($rows, function ($r) use ($col, $own) { return $r[$col] === $own; }));
        }
        $ids = array_map(function ($r) use ($s) { return (int) $r[$s['pk']]; }, $rows);
        $i = array_search((int) $id, $ids, true);
        $j = $i === false ? false : $i + ($dir < 0 ? -1 : 1);
        if ($i === false || $j < 0 || $j >= count($ids)) {
            return false;
        }
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        // Renumber the whole list so the order stays dense and unambiguous
        $stmt = $this->getConn()->prepare("UPDATE {$s['table']} SET {$s['order']} = :o WHERE {$s['pk']} = :id");
        foreach ($ids as $pos => $rowId) {
            $stmt->execute([':o' => $pos + 1, ':id' => $rowId]);
        }
        return true;
    }

    public function texts()
    {
        $out = array_fill_keys(array_keys(self::TEXTS), '');
        foreach ($this->getConn()->query("SELECT set_key, set_value FROM site_setting") as $row) {
            if (array_key_exists($row['set_key'], $out)) {
                $out[$row['set_key']] = (string) $row['set_value'];
            }
        }
        return $out;
    }

    public function saveText($key, $value)
    {
        $stmt = $this->getConn()->prepare(
            "INSERT INTO site_setting (set_key, set_value) VALUES (:k, :v) ON DUPLICATE KEY UPDATE set_value = VALUES(set_value)"
        );
        return $stmt->execute([':k' => $key, ':v' => $value]);
    }

    // Everything the pages need, in display order. People carry their entity's
    // name and logo; institutions are keyed by id for the entity picker.
    public function publicContent()
    {
        $out = ['texts' => $this->texts()];
        foreach (array_keys(self::SECTIONS) as $key) {
            $out[$key] = $this->all($key);
        }
        $byId = [];
        foreach ($out['institutions'] as $ins) {
            $byId[(int) $ins['ins_id']] = $ins;
        }
        foreach ($out['people'] as &$p) {
            $p['entity'] = $byId[(int) $p['fk_site_institution_ins_id']] ?? null;
        }
        unset($p);
        $out['institutions_by_id'] = $byId;
        return $out;
    }
}
