import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(page: Page, username: string, password: string = 'testpass123') {
  await page.goto('/login');
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\//);
}

export async function queryDb(sql: string, params: any[] = []): Promise<any[]> {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

export async function getEntitiesBySubtype(subtype: string): Promise<any[]> {
  return queryDb(
    `SELECT e.* FROM elgg_entities e
     JOIN elgg_entity_subtypes s ON s.id = e.subtype
     WHERE s.subtype = ? ORDER BY e.guid DESC`,
    [subtype]
  );
}

export async function getMetadata(entityGuid: number, name: string): Promise<any[]> {
  return queryDb(
    'SELECT * FROM elgg_metadata WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}

export async function getRelationship(
  guid_one: number,
  relationship: string,
  guid_two: number
): Promise<any[]> {
  return queryDb(
    `SELECT * FROM elgg_entity_relationships
     WHERE guid_one = ? AND relationship = ? AND guid_two = ?`,
    [guid_one, relationship, guid_two]
  );
}

export async function getAnnotations(entityGuid: number, name: string): Promise<any[]> {
  return queryDb(
    'SELECT * FROM elgg_annotations WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}
